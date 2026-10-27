<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman absensi hari ini.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now('Asia/Jakarta');

        $attendance = Attendance::with(['location', 'clockOutLocation'])
            ->where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        $location = $user->location;
        $canClockInLocation = $location !== null && (bool) $location->is_active;
        $isFriday = $now->dayOfWeekIso === 5;

        return view('attendance.index', compact(
            'user',
            'attendance',
            'location',
            'canClockInLocation',
            'isFriday',
            'now'
        ));
    }

    /**
     * Memproses absensi masuk dengan selfie dan validasi radius GPS.
     */
    public function storeClockIn(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $isFriday = $now->dayOfWeekIso === 5;
        $settings = SystemSetting::current();

        if (!$settings) {
            return back()->with('error', 'Pengaturan sistem belum tersedia. Hubungi Admin/HR.');
        }

        if ($now->isWeekend()) {
            return back()->with('error', 'Absensi hanya tersedia pada hari kerja.');
        }

        if (!$user->start_date || !$user->end_date) {
            return back()->with('error', 'Periode magang belum ditentukan oleh HR/Admin.');
        }

        $mulai = Carbon::parse($user->start_date)->toDateString();
        $selesai = Carbon::parse($user->end_date)->toDateString();

        if ($selesai < $mulai || $today < $mulai || $today > $selesai) {
            return back()->with(
                'error',
                "Tanggal absensi {$today} berada di luar periode magang ({$mulai} sampai {$selesai})."
            );
        }

        $location = $user->location;

        if (!$location || !$location->is_active) {
            return back()->with(
                'error',
                'Lokasi kantor aktif belum ditetapkan untuk akun Anda. Hubungi Admin/HR.'
            );
        }

        $batasMasuk = $now->copy()->setTimeFromTimeString($settings->clock_in_start);
        $waktuAkhirMasuk = $isFriday
            ? $settings->friday_clock_in_end
            : $settings->clock_in_end;
        $batasAkhir = $now->copy()->setTimeFromTimeString($waktuAkhirMasuk);
        $jadwalMulai = str_replace(':', '.', substr((string) $settings->clock_in_start, 0, 5));
        $jadwalAkhir = str_replace(':', '.', substr((string) $waktuAkhirMasuk, 0, 5));

        if ($now->lt($batasMasuk)) {
            return back()->with('error', "Absensi masuk tersedia mulai pukul {$jadwalMulai} WIB.");
        }

        if ($now->gte($batasAkhir)) {
            return back()->with('error', "Batas absensi masuk hari ini pukul {$jadwalAkhir} WIB telah berakhir.");
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'work_location' => [$isFriday ? 'required' : 'nullable', 'in:WFA,WFO'],
        ], [
            'photo.required' => 'Foto selfie wajib diambil sebelum absen.',
            'photo.image' => 'File selfie harus berupa gambar yang valid.',
            'photo.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'latitude.required' => 'Latitude GPS belum terdeteksi.',
            'latitude.numeric' => 'Data latitude tidak valid.',
            'latitude.between' => 'Nilai latitude tidak valid.',
            'longitude.required' => 'Longitude GPS belum terdeteksi.',
            'longitude.numeric' => 'Data longitude tidak valid.',
            'longitude.between' => 'Nilai longitude tidak valid.',
            'work_location.required' => 'Pilih WFA atau WFO untuk absensi hari Jumat.',
            'work_location.in' => 'Metode kerja harus WFA atau WFO.',
        ]);

        if (Attendance::where('user_id', $user->id)->whereDate('date', $today)->exists()) {
            return back()->with('error', 'Anda sudah melakukan absensi masuk hari ini.');
        }

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $distanceMeters = $this->distanceInMeters(
            $latitude,
            $longitude,
            (float) $location->latitude,
            (float) $location->longitude
        );

        // WFA pada Jumat tidak dibatasi radius kantor. WFO dan hari kerja biasa wajib berada dalam radius.
        $isWfa = $isFriday && ($validated['work_location'] ?? null) === 'WFA';

        if (!$isWfa && $distanceMeters > (int) $location->radius_meters) {
            return back()->with(
                'error',
                "Absensi ditolak. Jarak Anda {$distanceMeters} meter dari {$location->name}, sedangkan radius yang diizinkan {$location->radius_meters} meter."
            );
        }

        $status = $now->greaterThan($batasMasuk) ? 'Terlambat' : 'Hadir';
        $photoPath = $request->file('photo')->store('attendance/selfies', 'local');

        if (!is_string($photoPath) || $photoPath === '') {
            return back()->with('error', 'Foto gagal disimpan. Silakan coba kembali.');
        }

        try {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => $now->format('H:i:s'),
                'clock_out' => null,
                'work_location' => $isFriday ? $validated['work_location'] : null,
                'status' => $status,
                'notes' => 'Absen masuk via DARIN - Kementerian ESDM',
                'photo_path' => $photoPath,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_id' => $location->id,
                'location_name' => $location->name,
                'distance_meters' => $distanceMeters,
            ]);
        } catch (QueryException $e) {
            Storage::disk('local')->delete($photoPath);

            if (Attendance::where('user_id', $user->id)->whereDate('date', $today)->exists()) {
                return back()->with('error', 'Absensi hari ini sudah tercatat.');
            }

            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($photoPath);
            throw $e;
        }

        $pesan = 'Absensi masuk berhasil dicatat pukul '
            . $now->format('H:i') . ' WIB. Status: ' . $status
            . '. Jarak dari lokasi ' . $location->name . ': ' . $distanceMeters . ' meter.';

        if ($isFriday) {
            $pesan .= ' Metode kerja: ' . $validated['work_location'] . '.';
        }

        return redirect()->route('absen')->with('success', $pesan);
    }

    /**
     * Memproses absensi pulang dengan selfie dan validasi radius GPS terbaru.
     */
    public function storeClockOut(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $settings = SystemSetting::current();

        if (!$settings) {
            return back()->with('error', 'Pengaturan sistem belum tersedia. Hubungi Admin/HR.');
        }

        if ($now->isWeekend()) {
            return back()->with('error', 'Absensi pulang tidak tersedia pada akhir pekan.');
        }

        if (!$user->start_date || !$user->end_date) {
            return back()->with('error', 'Periode magang belum ditentukan oleh HR/Admin.');
        }

        $mulai = Carbon::parse($user->start_date)->toDateString();
        $selesai = Carbon::parse($user->end_date)->toDateString();

        if ($selesai < $mulai || $today < $mulai || $today > $selesai) {
            return back()->with(
                'error',
                "Tanggal absensi {$today} berada di luar periode magang ({$mulai} sampai {$selesai})."
            );
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Silakan lakukan absen masuk terlebih dahulu.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah melakukan absensi pulang hari ini.');
        }

        $waktuPulang = $now->dayOfWeekIso === 5
            ? $settings->friday_clock_out_time
            : $settings->clock_out_time;
        $batasPulang = $now->copy()->setTimeFromTimeString($waktuPulang);
        $jadwal = str_replace(':', '.', substr((string) $waktuPulang, 0, 5)) . ' WIB';

        if ($now->lt($batasPulang)) {
            return back()->with('error', "Absensi pulang baru tersedia mulai pukul {$jadwal}.");
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'photo.required' => 'Foto selfie baru wajib diambil untuk absensi pulang.',
            'photo.image' => 'File selfie harus berupa gambar yang valid.',
            'photo.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'latitude.required' => 'Latitude GPS terbaru belum terdeteksi.',
            'latitude.numeric' => 'Data latitude GPS tidak valid.',
            'latitude.between' => 'Nilai latitude tidak valid.',
            'longitude.required' => 'Longitude GPS terbaru belum terdeteksi.',
            'longitude.numeric' => 'Data longitude GPS tidak valid.',
            'longitude.between' => 'Nilai longitude tidak valid.',
        ]);

        // Gunakan lokasi yang tersimpan saat clock-in, bukan lokasi yang mungkin baru diubah oleh Admin.
        $location = $attendance->location_id
            ? Location::find($attendance->location_id)
            : $user->location;

        if (!$location) {
            return back()->with(
                'error',
                'Lokasi absensi tidak ditemukan. Hubungi Admin/HR untuk memeriksa penempatan lokasi.'
            );
        }

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $distanceMeters = $this->distanceInMeters(
            $latitude,
            $longitude,
            (float) $location->latitude,
            (float) $location->longitude
        );

        $isFriday = $now->dayOfWeekIso === 5;
        $isWfa = $isFriday && $attendance->work_location === 'WFA';

        if (!$isWfa && $distanceMeters > (int) $location->radius_meters) {
            return back()->with(
                'error',
                "Absensi pulang ditolak. Jarak Anda {$distanceMeters} meter dari {$location->name}, sedangkan radius yang diizinkan {$location->radius_meters} meter."
            );
        }

        $photoPath = $request->file('photo')->store('attendance/selfies', 'local');

        if (!is_string($photoPath) || $photoPath === '') {
            return back()->with('error', 'Foto absensi pulang gagal disimpan. Silakan coba kembali.');
        }

        try {
            $attendance->clock_out = $now->format('H:i:s');
            $attendance->clock_out_photo_path = $photoPath;
            $attendance->clock_out_latitude = $latitude;
            $attendance->clock_out_longitude = $longitude;
            $attendance->clock_out_location_id = $location->id;
            $attendance->clock_out_location_name = $location->name;
            $attendance->clock_out_distance_meters = $distanceMeters;
            $attendance->save();
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($photoPath);
            throw $e;
        }

        return redirect()->route('absen')->with(
            'success',
            'Absensi pulang berhasil dicatat pukul ' . $now->format('H:i')
            . ' WIB. Jarak dari lokasi ' . $location->name . ': ' . $distanceMeters . ' meter.'
        );
    }

    /**
     * Menghitung jarak dalam meter menggunakan formula Haversine.
     */
    private function distanceInMeters(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): int {
        $earthRadius = 6371000;

        $latitudeDelta = deg2rad($latitude2 - $latitude1);
        $longitudeDelta = deg2rad($longitude2 - $longitude1);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude1))
            * cos(deg2rad($latitude2))
            * sin($longitudeDelta / 2) ** 2;

        $a = min(1, max(0, $a));
        $centralAngle = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $centralAngle);
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