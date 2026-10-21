<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman absensi.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now('Asia/Jakarta');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        return view(
            'attendance.index',
            compact('user', 'attendance')
        );
    }

    /**
     * Memproses absensi masuk.
     */
    public function storeClockIn(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $isFriday = $now->dayOfWeekIso === 5;
        $settings = SystemSetting::current();

        // Absensi hanya pada hari kerja.
        if ($now->isWeekend()) {
            return back()->with(
                'error',
                'Absensi hanya tersedia pada hari kerja.'
            );
        }

        // Pastikan periode magang tersedia.
        if (!$user->start_date || !$user->end_date) {
            return back()->with(
                'error',
                'Periode magang belum ditentukan oleh HR/Admin.'
            );
        }

        /*
         * Bandingkan tanggal dalam format YYYY-MM-DD.
         * Tidak membandingkan timestamp yang bisa terpengaruh zona waktu.
         */
        $mulai = Carbon::parse($user->start_date)->toDateString();
        $selesai = Carbon::parse($user->end_date)->toDateString();

        if (
            $selesai < $mulai ||
            $today < $mulai ||
            $today > $selesai
        ) {
            return back()->with(
                'error',
                "Tanggal absensi {$today} berada di luar periode magang ({$mulai} sampai {$selesai})."
            );
        }

        // Jadwal absensi masuk mengikuti Pengaturan Sistem.
        $batasMasuk = $now->copy()->setTimeFromTimeString(
            $settings->clock_in_start
        );

        $waktuAkhirMasuk = $isFriday
            ? $settings->friday_clock_in_end
            : $settings->clock_in_end;

        $batasAkhir = $now->copy()->setTimeFromTimeString($waktuAkhirMasuk);
        $jadwalMulai = str_replace(':', '.', substr((string) $settings->clock_in_start, 0, 5));
        $jadwalAkhir = str_replace(':', '.', substr((string) $waktuAkhirMasuk, 0, 5));

        if ($now->lt($batasMasuk)) {
            return back()->with(
                'error',
                "Absensi masuk tersedia mulai pukul {$jadwalMulai} WIB."
            );
        }

        if ($now->gte($batasAkhir)) {
            return back()->with(
                'error',
                "Batas absensi masuk hari ini pukul {$jadwalAkhir} WIB telah berakhir."
            );
        }

        // Validasi data absensi.
        $validated = $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'work_location' => [
                $isFriday ? 'required' : 'nullable',
                'in:WFA,WFO',
            ],
        ], [
            'photo.required' =>
                'Foto selfie wajib diambil sebelum absen.',
            'photo.image' =>
                'File selfie harus berupa gambar yang valid.',
            'photo.mimes' =>
                'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'photo.max' =>
                'Ukuran foto maksimal 2 MB.',
            'latitude.required' =>
                'Latitude GPS belum terdeteksi.',
            'latitude.numeric' =>
                'Data latitude tidak valid.',
            'latitude.between' =>
                'Nilai latitude tidak valid.',
            'longitude.required' =>
                'Longitude GPS belum terdeteksi.',
            'longitude.numeric' =>
                'Data longitude tidak valid.',
            'longitude.between' =>
                'Nilai longitude tidak valid.',
            'work_location.required' =>
                'Pilih WFA atau WFO untuk absensi hari Jumat.',
            'work_location.in' =>
                'Metode kerja harus WFA atau WFO.',
        ]);

        // Cegah absensi masuk ganda pada tanggal yang sama.
        $sudahAbsen = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        if ($sudahAbsen) {
            return back()->with(
                'error',
                'Anda sudah melakukan absensi masuk hari ini.'
            );
        }

        // Status berdasarkan waktu masuk.
        $status = $now->greaterThan($batasMasuk)
            ? 'Terlambat'
            : 'Hadir';

        // Simpan foto selfie.
        $photoPath = $request->file('photo')->store(
            'selfies',
            'public'
        );

        if (!$photoPath) {
            return back()->with(
                'error',
                'Foto gagal disimpan. Silakan coba kembali.'
            );
        }

        try {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => $now->format('H:i:s'),
                'clock_out' => null,
                'work_location' => $isFriday
                    ? $validated['work_location']
                    : null,
                'status' => $status,
                'notes' => 'Absen masuk via DARIN - Kementerian ESDM',
                'photo_path' => $photoPath,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        } catch (QueryException $e) {
            Storage::disk('public')->delete($photoPath);

            if (
                Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->exists()
            ) {
                return back()->with(
                    'error',
                    'Absensi hari ini sudah tercatat.'
                );
            }

            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($photoPath);

            throw $e;
        }

        $pesan = 'Absensi masuk berhasil dicatat pukul '
            . $now->format('H:i')
            . ' WIB. Status: '
            . $status
            . '.';

        if ($isFriday) {
            $pesan .= ' Metode kerja: '
                . $validated['work_location']
                . '.';
        }

        return redirect()
            ->route('absen')
            ->with('success', $pesan);
    }

    /**
     * Memproses absensi pulang.
     */
    public function storeClockOut(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $settings = SystemSetting::current();

        if ($now->isWeekend()) {
            return back()->with(
                'error',
                'Absensi pulang tidak tersedia pada akhir pekan.'
            );
        }

        if (!$user->start_date || !$user->end_date) {
            return back()->with(
                'error',
                'Periode magang belum ditentukan oleh HR/Admin.'
            );
        }

        // Bandingkan tanggal saja.
        $mulai = Carbon::parse($user->start_date)->toDateString();
        $selesai = Carbon::parse($user->end_date)->toDateString();

        if (
            $selesai < $mulai ||
            $today < $mulai ||
            $today > $selesai
        ) {
            return back()->with(
                'error',
                "Tanggal absensi {$today} berada di luar periode magang ({$mulai} sampai {$selesai})."
            );
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return back()->with(
                'error',
                'Silakan lakukan absen masuk terlebih dahulu.'
            );
        }

        if ($attendance->clock_out) {
            return back()->with(
                'error',
                'Anda sudah melakukan absensi pulang hari ini.'
            );
        }

        // Jadwal absensi pulang mengikuti Pengaturan Sistem.
        $waktuPulang = $now->dayOfWeekIso === 5
            ? $settings->friday_clock_out_time
            : $settings->clock_out_time;

        $batasPulang = $now->copy()->setTimeFromTimeString($waktuPulang);
        $jadwal = str_replace(':', '.', substr((string) $waktuPulang, 0, 5)) . ' WIB';

        if ($now->lt($batasPulang)) {
            return back()->with(
                'error',
                "Absensi pulang baru tersedia mulai pukul {$jadwal}."
            );
        }

        $attendance->clock_out = $now->format('H:i:s');
        $attendance->save();

        return redirect()
            ->route('absen')
            ->with(
                'success',
                'Absensi pulang berhasil dicatat pukul '
                . $now->format('H:i')
                . ' WIB.'
            );
    }

    /**
     * Mengambil riwayat absensi berdasarkan bulan.
     */
    private function riwayatBulanIni(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $bulan = $request->query('bulan');

        if (
            !is_string($bulan) ||
            !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)
        ) {
            $bulan = Carbon::now('Asia/Jakarta')->format('Y-m');
        }

        $awal = Carbon::createFromFormat(
            '!Y-m',
            $bulan,
            'Asia/Jakarta'
        );

        $akhir = $awal->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $awal->toDateString(),
                $akhir->toDateString(),
            ])
            ->orderBy('date')
            ->orderBy('clock_in')
            ->get();

        return [$user, $attendances, $awal];
    }

    /**
     * Ekspor riwayat absensi ke PDF.
     */
    public function exportPdf(Request $request)
    {
        [$user, $attendances, $periode] =
            $this->riwayatBulanIni($request);

        $pdf = Pdf::loadView(
            'attendance.export-pdf',
            compact('user', 'attendances', 'periode')
        );

        $namaFile = 'riwayat-absensi-'
            . Str::slug($user->name)
            . '-'
            . $periode->format('Y-m')
            . '.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * Ekspor riwayat absensi ke CSV untuk Excel.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        [$user, $attendances, $periode] =
            $this->riwayatBulanIni($request);

        $namaFile = 'riwayat-absensi-'
            . Str::slug($user->name)
            . '-'
            . $periode->format('Y-m')
            . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' =>
                'attachment; filename="' . $namaFile . '"',
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            // BOM agar UTF-8 terbaca dengan benar di Excel.
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Tanggal',
                'Jam Masuk',
                'Jam Pulang',
                'Metode Kerja',
                'Status',
                'Latitude',
                'Longitude',
                'Foto',
                'Catatan',
            ], ';', '"', '\\');

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date
                        ? Carbon::parse($attendance->date)
                            ->translatedFormat('l, d F Y')
                        : '-',
                    $attendance->clock_in ?? '-',
                    $attendance->clock_out ?? '-',
                    $attendance->work_location ?? '-',
                    $attendance->status ?? '-',
                    $attendance->latitude ?? '-',
                    $attendance->longitude ?? '-',
                    $attendance->photo_path ?? '-',
                    $attendance->notes ?? '-',
                ], ';', '"', '\\');
            }

            fclose($file);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }
}