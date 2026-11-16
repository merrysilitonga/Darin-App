<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    /** Maksimal panjang rentang tanggal sekali input. */
    private const MAX_RANGE_DAYS = 31;

    /** Maksimal baris data pada file CSV impor. */
    private const MAX_IMPORT_ROWS = 500;

    /** Format tanggal yang diterima pada file CSV. */
    private const CSV_DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y'];

    /**
     * Menampilkan daftar hari libur dan kalender bulanan.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tahun' => [
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
            ],
            'bulan' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $today = Carbon::today('Asia/Jakarta');

        $tahun = (int) ($validated['tahun'] ?? $today->year);

        // Bulan default: bulan berjalan jika tahun yang dipilih = tahun ini.
        $bulan = (int) (
            $validated['bulan'] ?? ($tahun === $today->year ? $today->month : 1)
        );

        $search = trim($validated['q'] ?? '');

        $query = Holiday::query()
            ->whereYear('date', $tahun);

        // Filter berdasarkan nama atau keterangan hari libur.
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Statistik berdasarkan tahun yang dipilih.
        $totalHolidays = (clone $query)->count();

        $activeHolidays = (clone $query)
            ->where('is_active', true)
            ->count();

        $inactiveHolidays = (clone $query)
            ->where('is_active', false)
            ->count();

        // Daftar hari libur.
        $holidays = (clone $query)
            ->orderBy('date')
            ->paginate(15)
            ->withQueryString();

        // Kalender bulanan.
        $calendar = $this->buildCalendar($tahun, $bulan, $today);

        return view('admin.holidays.index', compact(
            'holidays',
            'tahun',
            'bulan',
            'search',
            'totalHolidays',
            'activeHolidays',
            'inactiveHolidays',
            'calendar'
        ));
    }

    /**
     * Menyimpan hari libur baru (satu tanggal atau rentang tanggal).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date',
            ],
            'skip_weekend' => [
                'nullable',
                'boolean',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'type' => [
                'required',
                Rule::in(Holiday::TYPES),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ], [
            'date.required' => 'Tanggal hari libur wajib diisi.',
            'date.date_format' => 'Format tanggal tidak valid.',
            'end_date.date_format' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'name.max' => 'Nama hari libur maksimal 150 karakter.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis hari libur tidak valid.',
            'description.max' => 'Keterangan maksimal 2.000 karakter.',
            'is_active.required' => 'Status hari libur wajib ditentukan.',
        ]);

        $mulai = Carbon::createFromFormat('!Y-m-d', $validated['date'], 'Asia/Jakarta');
        $selesai = !empty($validated['end_date'])
            ? Carbon::createFromFormat('!Y-m-d', $validated['end_date'], 'Asia/Jakarta')
            : $mulai->copy();

        $isRange = $selesai->gt($mulai);

        if ($mulai->diffInDays($selesai) + 1 > self::MAX_RANGE_DAYS) {
            return back()
                ->withInput()
                ->withErrors([
                    'end_date' => 'Rentang tanggal maksimal ' . self::MAX_RANGE_DAYS . ' hari.',
                ]);
        }

        $skipWeekend = $isRange && (bool) ($validated['skip_weekend'] ?? false);

        // Daftar tanggal yang akan dibuat.
        $tanggalList = [];

        foreach (CarbonPeriod::create($mulai, $selesai) as $tanggal) {
            if ($skipWeekend && $tanggal->isWeekend()) {
                continue;
            }

            $tanggalList[] = $tanggal->toDateString();
        }

        if ($tanggalList === []) {
            return back()
                ->withInput()
                ->withErrors([
                    'end_date' => 'Rentang tanggal hanya berisi Sabtu/Minggu. Tidak ada tanggal yang disimpan.',
                ]);
        }

        $sudahAda = $this->existingDates($tanggalList);

        if (!$isRange && $sudahAda !== []) {
            return back()
                ->withInput()
                ->withErrors([
                    'date' => 'Tanggal tersebut sudah terdaftar sebagai hari libur.',
                ]);
        }

        $tanggalBaru = array_values(array_diff($tanggalList, $sudahAda));

        if ($tanggalBaru === []) {
            return back()
                ->withInput()
                ->withErrors([
                    'date' => 'Semua tanggal pada rentang tersebut sudah terdaftar sebagai hari libur.',
                ]);
        }

        DB::transaction(function () use ($tanggalBaru, $validated) {
            foreach ($tanggalBaru as $tanggal) {
                Holiday::create([
                    'date' => $tanggal,
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => (bool) $validated['is_active'],
                ]);
            }
        });

        $pesan = $isRange
            ? count($tanggalBaru) . ' hari libur berhasil ditambahkan.'
            : 'Hari libur berhasil ditambahkan.';

        if ($sudahAda !== []) {
            $pesan .= ' ' . count($sudahAda) . ' tanggal dilewati karena sudah terdaftar: '
                . $this->formatDateList($sudahAda) . '.';
        }

        return redirect()
            ->route('admin.holidays', [
                'tahun' => substr($tanggalBaru[0], 0, 4),
                'bulan' => (int) substr($tanggalBaru[0], 5, 2),
            ])
            ->with('success', $pesan);
    }

    /**
     * Memperbarui data hari libur.
     */
    public function update(Request $request, int $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'type' => [
                'required',
                Rule::in(Holiday::TYPES),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ], [
            'date.required' => 'Tanggal hari libur wajib diisi.',
            'date.date_format' => 'Format tanggal tidak valid.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'name.max' => 'Nama hari libur maksimal 150 karakter.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis hari libur tidak valid.',
            'description.max' => 'Keterangan maksimal 2.000 karakter.',
        ]);

        // Tanggal tidak boleh bentrok dengan hari libur lain.
        $bentrok = Holiday::query()
            ->whereDate('date', $validated['date'])
            ->whereKeyNot($holiday->id)
            ->exists();

        if ($bentrok) {
            return back()
                ->withInput()
                ->withErrors([
                    'date' => 'Tanggal tersebut sudah digunakan oleh hari libur lain.',
                ]);
        }

        $holiday->update($validated);

        return redirect()
            ->route('admin.holidays', [
                'tahun' => substr($validated['date'], 0, 4),
                'bulan' => (int) substr($validated['date'], 5, 2),
            ])
            ->with('success', 'Data hari libur berhasil diperbarui.');
    }

    /**
     * Menghapus data hari libur.
     */
    public function destroy(int $id)
    {
        $holiday = Holiday::findOrFail($id);

        $tahun = $holiday->date->format('Y');
        $bulan = (int) $holiday->date->format('n');

        $holiday->delete();

        return redirect()
            ->route('admin.holidays', [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ])
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Impor hari libur dari file CSV.
     * Kolom: tanggal;nama;jenis;keterangan;aktif (keterangan & aktif opsional).
     * Jika ada baris yang tidak valid, tidak ada data yang disimpan.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:1024',
            ],
        ], [
            'file.required' => 'File CSV wajib dipilih.',
            'file.mimes' => 'File harus berformat .csv.',
            'file.max' => 'Ukuran file maksimal 1 MB.',
        ]);

        $content = (string) file_get_contents($request->file('file')->getRealPath());

        // Hapus BOM UTF-8 (biasanya dari Excel).
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (trim($content) === '') {
            return $this->importFailed(['File CSV kosong.']);
        }

        // Deteksi pemisah kolom dari baris pertama (Excel Indonesia memakai ";").
        $barisPertama = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($barisPertama, ';') >= substr_count($barisPertama, ',')
            ? ';'
            : ',';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $rows = [];
        $errors = [];
        $tanggalDiFile = [];
        $nomorBaris = 0;

        while (($kolom = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
            $nomorBaris++;

            $kolom = array_map(
                fn ($nilai) => trim((string) $nilai),
                $kolom
            );

            // Lewati baris kosong.
            if (implode('', $kolom) === '') {
                continue;
            }

            // Baris pertama yang bukan tanggal dianggap header.
            if ($nomorBaris === 1 && $this->parseCsvDate($kolom[0]) === null) {
                continue;
            }

            if (count($rows) + count($errors) >= self::MAX_IMPORT_ROWS) {
                $errors[] = 'Maksimal ' . self::MAX_IMPORT_ROWS . ' baris data per file.';
                break;
            }

            $hasil = $this->parseCsvRow($kolom, $nomorBaris, $tanggalDiFile);

            if (is_string($hasil)) {
                $errors[] = $hasil;
                continue;
            }

            $tanggalDiFile[$hasil['date']] = $nomorBaris;
            $rows[] = $hasil;
        }

        fclose($stream);

        if ($errors !== []) {
            return $this->importFailed($errors);
        }

        if ($rows === []) {
            return $this->importFailed(['Tidak ada baris data yang dapat diimpor.']);
        }

        $sudahAda = $this->existingDates(array_column($rows, 'date'));

        $rowsBaru = array_values(array_filter(
            $rows,
            fn ($row) => !in_array($row['date'], $sudahAda, true)
        ));

        DB::transaction(function () use ($rowsBaru) {
            foreach ($rowsBaru as $row) {
                Holiday::create($row);
            }
        });

        $pesan = count($rowsBaru) . ' hari libur berhasil diimpor.';

        if ($sudahAda !== []) {
            $pesan .= ' ' . count($sudahAda) . ' tanggal dilewati karena sudah terdaftar: '
                . $this->formatDateList($sudahAda) . '.';
        }

        $acuan = $rowsBaru[0]['date'] ?? $rows[0]['date'];

        return redirect()
            ->route('admin.holidays', [
                'tahun' => substr($acuan, 0, 4),
                'bulan' => (int) substr($acuan, 5, 2),
            ])
            ->with('success', $pesan);
    }

    /**
     * Unduh template CSV impor hari libur.
     */
    public function template()
    {
        $tahun = now('Asia/Jakarta')->year;

        $baris = [
            ['tanggal', 'nama', 'jenis', 'keterangan', 'aktif'],
            ["{$tahun}-08-17", 'Hari Kemerdekaan Republik Indonesia', 'Nasional', 'Libur nasional', '1'],
            ["{$tahun}-12-24", 'Cuti Bersama Natal', 'Khusus ESDM', 'Cuti bersama', '1'],
            ["{$tahun}-12-25", 'Hari Raya Natal', 'Nasional', '', '1'],
        ];

        $stream = fopen('php://temp', 'r+');

        foreach ($baris as $kolom) {
            fputcsv($stream, $kolom, ';', '"', '');
        }

        rewind($stream);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-hari-libur.csv"',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * Data kalender bulanan (minggu dimulai Senin).
     */
    private function buildCalendar(int $tahun, int $bulan, Carbon $today): array
    {
        $awalBulan = Carbon::create($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta');

        $holidaysBulanIni = Holiday::query()
            ->whereDate('date', '>=', $awalBulan->toDateString())
            ->whereDate('date', '<=', $awalBulan->copy()->endOfMonth()->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn (Holiday $holiday) => $holiday->date->toDateString());

        $cursor = $awalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $akhir = $awalBulan->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];

        while ($cursor->lte($akhir)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->toDateString();

                $week[] = [
                    'date' => $cursor->copy(),
                    'in_month' => $cursor->month === $bulan,
                    'is_today' => $key === $today->toDateString(),
                    'is_weekend' => $cursor->isWeekend(),
                    'holiday' => $holidaysBulanIni->get($key),
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        $sebelumnya = $awalBulan->copy()->subMonth();
        $berikutnya = $awalBulan->copy()->addMonth();

        return [
            'label' => $awalBulan->locale('id')->translatedFormat('F Y'),
            'weeks' => $weeks,
            'holidays' => $holidaysBulanIni->values(),
            'prev' => $sebelumnya->year >= 2000
                ? ['tahun' => $sebelumnya->year, 'bulan' => $sebelumnya->month]
                : null,
            'next' => $berikutnya->year <= 2100
                ? ['tahun' => $berikutnya->year, 'bulan' => $berikutnya->month]
                : null,
        ];
    }

    /**
     * Tanggal (Y-m-d) dari daftar yang sudah terdaftar di tabel holidays.
     */
    private function existingDates(array $tanggalList): array
    {
        if ($tanggalList === []) {
            return [];
        }

        sort($tanggalList);

        return Holiday::query()
            ->whereDate('date', '>=', $tanggalList[0])
            ->whereDate('date', '<=', end($tanggalList))
            ->get(['date'])
            ->map(fn (Holiday $holiday) => $holiday->date->toDateString())
            ->filter(fn (string $tanggal) => in_array($tanggal, $tanggalList, true))
            ->values()
            ->all();
    }

    /**
     * Validasi satu baris CSV. Mengembalikan data siap simpan atau pesan error.
     */
    private function parseCsvRow(array $kolom, int $nomorBaris, array $tanggalDiFile): array|string
    {
        $prefix = "Baris {$nomorBaris}: ";

        $tanggal = $this->parseCsvDate($kolom[0] ?? '');

        if ($tanggal === null) {
            return $prefix . 'tanggal "' . ($kolom[0] ?? '') . '" tidak valid (gunakan YYYY-MM-DD atau DD/MM/YYYY).';
        }

        if (isset($tanggalDiFile[$tanggal])) {
            return $prefix . "tanggal {$tanggal} duplikat dengan baris {$tanggalDiFile[$tanggal]}.";
        }

        $nama = $kolom[1] ?? '';

        if ($nama === '') {
            return $prefix . 'nama hari libur wajib diisi.';
        }

        if (mb_strlen($nama) > 150) {
            return $prefix . 'nama hari libur maksimal 150 karakter.';
        }

        $jenis = $this->parseCsvType($kolom[2] ?? '');

        if ($jenis === null) {
            return $prefix . 'jenis harus "Nasional" atau "Khusus ESDM".';
        }

        $keterangan = $kolom[3] ?? '';

        if (mb_strlen($keterangan) > 2000) {
            return $prefix . 'keterangan maksimal 2.000 karakter.';
        }

        $aktif = $this->parseCsvBoolean($kolom[4] ?? '');

        if ($aktif === null) {
            return $prefix . 'kolom aktif harus 1/0 atau ya/tidak.';
        }

        return [
            'date' => $tanggal,
            'name' => $nama,
            'type' => $jenis,
            'description' => $keterangan !== '' ? $keterangan : null,
            'is_active' => $aktif,
        ];
    }

    /**
     * Tanggal CSV → Y-m-d, atau null jika tidak valid.
     */
    private function parseCsvDate(string $nilai): ?string
    {
        $nilai = trim($nilai);

        foreach (self::CSV_DATE_FORMATS as $format) {
            $tanggal = DateTime::createFromFormat('!' . $format, $nilai);

            // Round-trip mencegah tanggal "meluap" (mis. 31/02 menjadi 03/03).
            if ($tanggal !== false && $tanggal->format($format) === $nilai) {
                $tahun = (int) $tanggal->format('Y');

                if ($tahun < 2000 || $tahun > 2100) {
                    return null;
                }

                return $tanggal->format('Y-m-d');
            }
        }

        return null;
    }

    private function parseCsvType(string $nilai): ?string
    {
        $nilai = mb_strtolower(trim($nilai));

        if ($nilai === '' || $nilai === 'nasional' || $nilai === 'libur nasional') {
            return 'Nasional';
        }

        if (str_contains($nilai, 'esdm') || str_contains($nilai, 'khusus')) {
            return 'Khusus ESDM';
        }

        return null;
    }

    private function parseCsvBoolean(string $nilai): ?bool
    {
        $nilai = mb_strtolower(trim($nilai));

        if (in_array($nilai, ['', '1', 'ya', 'y', 'aktif', 'true'], true)) {
            return true;
        }

        if (in_array($nilai, ['0', 'tidak', 't', 'nonaktif', 'false'], true)) {
            return false;
        }

        return null;
    }

    private function importFailed(array $errors)
    {
        $errors = array_slice($errors, 0, 20);

        return back()->withErrors($errors, 'import');
    }

    /**
     * Ringkas daftar tanggal untuk pesan notifikasi.
     */
    private function formatDateList(array $tanggalList): string
    {
        $tampil = array_map(
            fn (string $tanggal) => Carbon::parse($tanggal)->format('d-m-Y'),
            array_slice($tanggalList, 0, 5)
        );

        $sisa = count($tanggalList) - count($tampil);

        return implode(', ', $tampil) . ($sisa > 0 ? " dan {$sisa} lainnya" : '');
    }
}
