<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Logbook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LogbookController extends Controller
{
    /**
     * Menampilkan logbook berdasarkan bulan yang dipilih.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $bulan = $request->query('bulan');

        if (
            !is_string($bulan) ||
            !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)
        ) {
            $bulan = Carbon::now('Asia/Jakarta')->format('Y-m');
        }

        $periode = Carbon::createFromFormat(
            '!Y-m',
            $bulan,
            'Asia/Jakarta'
        );

        $monthStart = $periode->copy()->startOfMonth()->toDateString();
        $monthEnd = $periode->copy()->endOfMonth()->toDateString();

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $monthLabel = $namaBulan[(int) $periode->format('n')]
            . ' '
            . $periode->format('Y');

        $logbooks = Logbook::where('user_id', $user->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Satu entri yang masih menunggu berarti bulan masih terkunci.
        $monthLocked = $logbooks->contains(function ($item) {
            return $item->status === 'Menunggu'
                && $item->submitted_at !== null;
        });

        $totalCatatan = $logbooks->count();

        $menungguPersetujuan = $logbooks
            ->where('status', 'Menunggu')
            ->count();

        $disetujuiMentor = $logbooks
            ->where('status', 'Disetujui')
            ->count();

        $canSubmit = !$monthLocked
            && $logbooks->contains(function ($item) {
                return in_array(
                    $item->status,
                    ['Draft', 'Ditolak'],
                    true
                );
            });

        if ($monthLocked) {
            $monthStatus = 'Menunggu Approval';
        } elseif (
            $totalCatatan > 0 &&
            $logbooks->every(
                fn ($item) => $item->status === 'Disetujui'
            )
        ) {
            $monthStatus = 'Disetujui';
        } else {
            $monthStatus = 'Draft';
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $defaultDate = str_starts_with($today, $bulan)
            ? $today
            : $monthStart;

        return view('logbook.index', compact(
            'user',
            'logbooks',
            'bulan',
            'periode',
            'monthStart',
            'monthEnd',
            'monthLabel',
            'monthLocked',
            'monthStatus',
            'canSubmit',
            'totalCatatan',
            'menungguPersetujuan',
            'disetujuiMentor',
            'defaultDate'
        ));
    }

    /**
     * Menyimpan satu entri sebagai Draft.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['required', 'string', 'max:5000'],
        ], [
            'date.required' => 'Tanggal kegiatan wajib diisi.',
            'description.required' => 'Deskripsi pekerjaan wajib diisi.',
            'description.max' => 'Deskripsi maksimal 5.000 karakter.',
        ]);

        $user = $request->user();
        $date = $validated['date'];
        $bulan = $validated['bulan'];

        $this->ensureDateBelongsToMonth($date, $bulan);
        $this->ensureMonthIsOpen($user->id, $date);
        $this->validateLogbookDate($user, $date);
        $this->validateUniqueDate($user->id, $date);

        Logbook::create([
            'user_id' => $user->id,
            'date' => $date,
            'topic' => $this->makeTopic($validated['description']),
            'description' => trim($validated['description']),
            'status' => 'Draft',
            'submitted_at' => null,
        ]);

        return redirect()
            ->route('logbook', ['bulan' => $bulan])
            ->with('success', 'Entri logbook berhasil disimpan sebagai Draft.');
    }

    /**
     * Mengedit Draft atau entri yang ditolak.
     */
    public function update(Request $request, int $id)
    {
        $user = $request->user();

        $logbook = Logbook::where('user_id', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'bulan' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        if (!in_array($logbook->status, ['Draft', 'Ditolak'], true)) {
            return back()->with(
                'error',
                'Entri yang sudah dikirim atau disetujui tidak bisa diedit.'
            );
        }

        $date = $validated['date'];
        $bulan = $validated['bulan'];

        $this->ensureDateBelongsToMonth($date, $bulan);
        $this->ensureMonthIsOpen($user->id, $logbook->date->toDateString());
        $this->ensureMonthIsOpen($user->id, $date);
        $this->validateLogbookDate($user, $date);
        $this->validateUniqueDate($user->id, $date, $logbook->id);

        $logbook->update([
            'date' => $date,
            'topic' => $this->makeTopic($validated['description']),
            'description' => trim($validated['description']),
            'status' => 'Draft',
            'mentor_feedback' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'submitted_at' => null,
        ]);

        return redirect()
            ->route('logbook', ['bulan' => $bulan])
            ->with('success', 'Entri logbook berhasil diperbarui.');
    }

    /**
     * Menghapus Draft atau entri yang ditolak.
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $logbook = Logbook::where('user_id', $user->id)
            ->findOrFail($id);

        $bulan = $request->query(
            'bulan',
            $logbook->date->format('Y-m')
        );

        if (
            !is_string($bulan) ||
            !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)
        ) {
            $bulan = $logbook->date->format('Y-m');
        }

        if (!in_array($logbook->status, ['Draft', 'Ditolak'], true)) {
            return redirect()
                ->route('logbook', ['bulan' => $bulan])
                ->with('error', 'Entri yang sudah dikirim tidak bisa dihapus.');
        }

        $this->ensureMonthIsOpen(
            $user->id,
            $logbook->date->toDateString()
        );

        $logbook->delete();

        return redirect()
            ->route('logbook', ['bulan' => $bulan])
            ->with('success', 'Entri logbook berhasil dihapus.');
    }

    /**
     * Mengirim entri dalam bulan terpilih untuk diperiksa Mentor.
     */
    public function submitMonth(Request $request)
    {
        $validated = $request->validate([
            'bulan' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
        ]);

        $user = $request->user();
        $bulan = $validated['bulan'];

        $periode = Carbon::createFromFormat(
            '!Y-m',
            $bulan,
            'Asia/Jakarta'
        );

        $awal = $periode->copy()->startOfMonth()->toDateString();
        $akhir = $periode->copy()->endOfMonth()->toDateString();

        $logbooks = Logbook::where('user_id', $user->id)
            ->whereBetween('date', [$awal, $akhir])
            ->get();

        if ($logbooks->isEmpty()) {
            return back()->with(
                'error',
                'Belum ada entri yang dapat dikirim untuk approval.'
            );
        }

        if ($this->monthIsLocked($user->id, $awal)) {
            return back()->with(
                'error',
                'Bulan ini masih menunggu pemeriksaan Mentor.'
            );
        }

        $ids = $logbooks
            ->whereIn('status', ['Draft', 'Ditolak'])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return back()->with(
                'error',
                'Tidak ada entri Draft atau Ditolak untuk dikirim.'
            );
        }

        DB::transaction(function () use ($ids) {
            Logbook::whereIn('id', $ids)->update([
                'status' => 'Menunggu',
                'submitted_at' => now('Asia/Jakarta'),
                'mentor_feedback' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
        });

        return redirect()
            ->route('logbook', ['bulan' => $bulan])
            ->with(
                'success',
                'Logbook bulan ini berhasil dikirim untuk approval. Entri akan terkunci sampai pemeriksaan selesai.'
            );
    }

    /**
     * Memastikan tanggal entri sama dengan bulan yang sedang dipilih.
     */
    private function ensureDateBelongsToMonth(
        string $date,
        string $bulan
    ): void {
        if (substr($date, 0, 7) !== $bulan) {
            throw ValidationException::withMessages([
                'date' => 'Tanggal entri harus berada dalam bulan yang dipilih.',
            ]);
        }
    }

    /**
     * Validasi periode magang dan bukti absensi.
     */
    private function validateLogbookDate($user, string $date): void
    {
        if (!$user->start_date || !$user->end_date) {
            throw ValidationException::withMessages([
                'date' => 'Periode magang belum ditetapkan oleh HR/Admin.',
            ]);
        }

        $tanggal = Carbon::parse($date)->toDateString();
        $mulai = Carbon::parse($user->start_date)->toDateString();
        $selesai = Carbon::parse($user->end_date)->toDateString();

        if (
            $selesai < $mulai ||
            $tanggal < $mulai ||
            $tanggal > $selesai
        ) {
            throw ValidationException::withMessages([
                'date' => 'Tanggal logbook harus berada dalam periode magang Anda.',
            ]);
        }

        $hasAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $tanggal)
            ->whereNotNull('clock_in')
            ->exists();

        if (!$hasAttendance) {
            throw ValidationException::withMessages([
                'date' => 'Anda harus memiliki catatan absen masuk pada tanggal tersebut sebelum mengisi logbook.',
            ]);
        }
    }

    /**
     * Mencegah dua entri mahasiswa pada tanggal yang sama.
     */
    private function validateUniqueDate(
        int $userId,
        string $date,
        ?int $ignoreId = null
    ): void {
        $query = Logbook::where('user_id', $userId)
            ->whereDate('date', $date);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'date' => 'Anda sudah memiliki entri logbook pada tanggal tersebut.',
            ]);
        }
    }

    /**
     * Membuat judul internal dari baris pertama deskripsi.
     */
    private function makeTopic(string $description): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($description));
        $topic = trim($lines[0] ?? '');

        return Str::limit(
            $topic !== '' ? $topic : 'Kegiatan Magang',
            255,
            ''
        );
    }

    /**
     * Memastikan seluruh entri bulan belum sedang diperiksa.
     */
    private function ensureMonthIsOpen(
        int $userId,
        string $date
    ): void {
        if ($this->monthIsLocked($userId, $date)) {
            throw ValidationException::withMessages([
                'date' => 'Entri bulan ini terkunci karena sedang menunggu pemeriksaan Mentor.',
            ]);
        }
    }

    private function monthIsLocked(
        int $userId,
        string $date
    ): bool {
        $periode = Carbon::createFromFormat(
            '!Y-m',
            substr($date, 0, 7),
            'Asia/Jakarta'
        );

        return Logbook::where('user_id', $userId)
            ->whereBetween('date', [
                $periode->copy()->startOfMonth()->toDateString(),
                $periode->copy()->endOfMonth()->toDateString(),
            ])
            ->where('status', 'Menunggu')
            ->whereNotNull('submitted_at')
            ->exists();
    }
}