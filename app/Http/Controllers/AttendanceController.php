<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\SystemSetting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        // Hari yang sudah disetujui sebagai Izin/Sakit tidak perlu absen (form disembunyikan).
        $todayLeave = $attendance ? null : $this->approvedLeaveOn((int) $user->id, $now->toDateString());

        // Informasi kalender hari libur untuk peserta.
        $todayHoliday = Holiday::activeOn($now->toDateString());
        $upcomingHolidays = Holiday::upcoming($now->copy()->startOfDay(), 30, 3);

        return view('attendance.index', compact(
            'user',
            'attendance',
            'location',
            'canClockInLocation',
            'todayLeave',
            'isFriday',
            'now',
            'todayHoliday',
            'upcomingHolidays'
        ));
    }

    /**
     * Memproses absensi masuk dengan selfie,
     * validasi radius GPS, dan validasi kalender hari libur.
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

        /*
         * Validasi akhir pekan.
         * Sabtu dan Minggu tetap otomatis menjadi hari libur.
         */
        if ($now->isWeekend()) {
            return back()->with('error', 'Absensi hanya tersedia pada hari kerja.');
        }

        /*
         * VALIDASI KALENDER HARI LIBUR
         */
        $holiday = Holiday::activeOn($today);

        if ($holiday) {
            return back()->with(
                'error',
                "Hari ini ditetapkan sebagai hari libur: {$holiday->name}. Absensi masuk tidak tersedia."
            );
        }

        $periodError = $this->internshipPeriodError($user, $today);

        if ($periodError !== null) {
            return back()->with('error', $periodError);
        }

        /*
         * Hari Izin/Sakit yang sudah disetujui: absen biasa ditolak.
         * Pengecualian: tetap bertugas di luar kantor → absen sebagai Dinas Luar
         * (menunggu persetujuan Mentor).
         */
        $approvedLeave = $this->approvedLeaveOn((int) $user->id, $today);

        if ($approvedLeave !== null && $request->input('work_location') !== 'Dinas Luar') {
            return back()->with(
                'error',
                "Hari ini tercatat sebagai {$approvedLeave->request_type} yang sudah disetujui. Jika tetap bertugas, pilih Dinas Luar."
            )->withInput();
        }

        $location = $user->location;

        if (!$location || !$location->is_active) {
            return back()->with(
                'error',
                'Lokasi kantor aktif belum ditetapkan untuk akun Anda. Hubungi Admin/HR.'
            );
        }

        // Jam buka absen mulai pukul 06.00 pagi
        $jamBukaAbsen = $now->copy()->setTime(6, 0, 0);

        // Batas jam masuk tepat waktu (clock_in_start)
        $batasTepatWaktu = $now->copy()->setTimeFromTimeString($settings->clock_in_start);

        $waktuAkhirMasuk = $isFriday
            ? $settings->friday_clock_in_end
            : $settings->clock_in_end;

        $batasAkhir = $now->copy()->setTimeFromTimeString($waktuAkhirMasuk);

        $jadwalMulai = str_replace(':', '.', substr((string) $settings->clock_in_start, 0, 5));
        $jadwalAkhir = str_replace(':', '.', substr((string) $waktuAkhirMasuk, 0, 5));

        if ($now->lt($jamBukaAbsen)) {
            return back()->with('error', 'Absensi masuk baru dibuka mulai pukul 06.00 WIB.');
        }

        if ($now->gte($batasAkhir)) {
            return back()->with(
                'error',
                "Batas absensi masuk hari ini pukul {$jadwalAkhir} WIB telah berakhir."
            );
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            // Jumat: WFO/WFA/Dinas Luar (wajib). Hari lain: kosong (di kantor) atau Dinas Luar.
            'work_location' => [$isFriday ? 'required' : 'nullable', $isFriday ? 'in:WFA,WFO,Dinas Luar' : 'in:Dinas Luar'],
            'tujuan_dinas' => ['nullable', 'required_if:work_location,Dinas Luar', 'string', 'max:255'],
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
            'work_location.required' => 'Pilih metode kerja (WFO, WFA, atau Dinas Luar) untuk absensi hari Jumat.',
            'work_location.in' => $isFriday ? 'Metode kerja harus WFO, WFA, atau Dinas Luar.' : 'Metode kerja tidak valid.',
            'tujuan_dinas.required_if' => 'Tujuan / keterangan dinas wajib diisi untuk Dinas Luar.',
            'tujuan_dinas.max' => 'Tujuan dinas maksimal 255 karakter.',
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

        $metodeKerja = $validated['work_location'] ?? null;
        $isDinas = $metodeKerja === 'Dinas Luar';
        // WFA (hari Jumat) dan Dinas Luar tidak dibatasi radius; GPS tetap disimpan sebagai bukti.
        $isWfa = ($isFriday && $metodeKerja === 'WFA') || $isDinas;

        if (!$isWfa && $distanceMeters > (int) $location->radius_meters) {
            return back()->with(
                'error',
                "Absensi ditolak. Jarak Anda {$distanceMeters} meter dari {$location->name}, sedangkan radius yang diizinkan {$location->radius_meters} meter."
            );
        }

        // Status Hadir jika absen sebelum atau pas jam masuk, Terlambat jika melewati jam masuk
        $status = $now->greaterThan($batasTepatWaktu) ? 'Terlambat' : 'Hadir';

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
                'work_location' => $metodeKerja,
                // Dinas Luar wajib disetujui Mentor.
                'dinas_status' => $isDinas ? 'Menunggu' : null,
                'status' => $status,
                'notes' => $isDinas
                    ? 'Dinas Luar: ' . trim($validated['tujuan_dinas'])
                    : 'Absen masuk via DARIN - Kementerian ESDM',
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

        if ($metodeKerja) {
            $pesan .= ' Metode kerja: ' . $metodeKerja . '.';
        }

        if ($isDinas) {
            $pesan .= ' Dinas Luar menunggu persetujuan Mentor. Tetap lakukan absen pulang di akhir hari.';
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

        $periodError = $this->internshipPeriodError($user, $today);

        if ($periodError !== null) {
            return back()->with('error', $periodError);
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
            return back()->with(
                'error',
                "Absensi pulang baru tersedia mulai pukul {$jadwal}."
            );
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
        // Absen pulang tanpa batas radius untuk WFA (hari Jumat) dan Dinas Luar.
        $isWfa = ($isFriday && $attendance->work_location === 'WFA') || $attendance->work_location === 'Dinas Luar';

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
            $updated = Attendance::query()
                ->whereKey($attendance->getKey())
                ->whereNull('clock_out')
                ->update([
                    'clock_out' => $now->format('H:i:s'),
                    'clock_out_photo_path' => $photoPath,
                    'clock_out_latitude' => $latitude,
                    'clock_out_longitude' => $longitude,
                    'clock_out_location_id' => $location->id,
                    'clock_out_location_name' => $location->name,
                    'clock_out_distance_meters' => $distanceMeters,
                ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($photoPath);
            throw $e;
        }

        if ($updated !== 1) {
            Storage::disk('local')->delete($photoPath);

            return back()->with(
                'error',
                'Absensi pulang sudah tercatat. Anda tidak dapat melakukan absensi pulang dua kali.'
            );
        }

        return redirect()->route('absen')->with(
            'success',
            'Absensi pulang berhasil dicatat pukul ' . $now->format('H:i')
            . ' WIB. Jarak dari lokasi ' . $location->name . ': ' . $distanceMeters . ' meter.'
        );
    }

    /**
     * Memvalidasi tanggal absensi terhadap periode magang.
     * Mengembalikan pesan error, atau null jika valid.
     */
    private function internshipPeriodError(User $user, string $today): ?string
    {
        if (!$user->start_date || !$user->end_date) {
            return 'Periode magang belum ditentukan oleh HR/Admin.';
        }

        $mulai = Carbon::parse($user->start_date)->toDateString();
        $selesai = Carbon::parse($user->end_date)->toDateString();

        if ($selesai < $mulai || $today < $mulai || $today > $selesai) {
            return "Tanggal absensi {$today} berada di luar periode magang ({$mulai} sampai {$selesai}).";
        }

        return null;
    }

    /**
     * Mengambil pengajuan Izin/Sakit berstatus Disetujui yang mencakup tanggal tertentu.
     */
    private function approvedLeaveOn(int $userId, string $date): ?LeaveRequest
    {
        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('status', 'Disetujui')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();
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
     * Menampilkan halaman riwayat absensi mahasiswa.
     */
    public function history(Request $request)
    {
        // Halaman riwayat menampilkan data terbaru lebih dulu.
        [$user, $attendances, $periode, $leaveRequests, $riwayat, $ringkasan] =
            $this->riwayatLengkap($request, 'desc');

        [$bulanPertama, $bulanTerakhir] = $this->rentangBulanMagang($user);

        return view(
            'attendance.history',
            compact('user', 'attendances', 'periode', 'leaveRequests', 'riwayat', 'ringkasan', 'bulanPertama', 'bulanTerakhir')
        );
    }

    /**
     * Riwayat absensi + izin/sakit satu bulan (dipakai halaman riwayat & ekspor).
     */
    private function riwayatLengkap(Request $request, string $direction = 'asc'): array
    {
        [$user, $attendances, $periode] = $this->riwayatBulanIni($request, $direction);

        $awal = $periode->copy()->startOfMonth();
        $akhir = $periode->copy()->endOfMonth();

        /*
         * Pengajuan izin/sakit yang tanggalnya beririsan dengan bulan terpilih,
         * atau yang dikirim pada bulan tersebut.
         */
        $leaveRequests = LeaveRequest::with('reviewer:id,name')
            ->where('user_id', $user->id)
            ->where(function ($query) use ($awal, $akhir) {
                $query->where(function ($rentang) use ($awal, $akhir) {
                    $rentang->whereDate('start_date', '<=', $akhir->toDateString())
                        ->whereDate('end_date', '>=', $awal->toDateString());
                })->orWhereBetween('created_at', [
                    $awal->copy()->startOfDay(),
                    $akhir->copy()->endOfDay(),
                ]);
            })
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $riwayat = $this->gabungRiwayat($attendances, $leaveRequests, $awal, $akhir, $direction, $user);
        $hariIzin = collect($riwayat)->where('jenis', 'izin')->where('leave.status', 'Disetujui');

        $absenSah = $attendances->reject(fn (Attendance $absen) => $absen->dinasDitolak());
        $tanggalIzinSah = $hariIzin->pluck('tanggal')->flip();
        // Dinas Luar ditolak tanpa izin disetujui pada tanggal itu dihitung Alfa.
        $dinasDitolakAlfa = $attendances
            ->filter(fn (Attendance $absen) => $absen->dinasDitolak())
            ->reject(fn (Attendance $absen) => $tanggalIzinSah->has(Carbon::parse($absen->date)->toDateString()))
            ->count();

        $ringkasan = [
            'hadir' => $absenSah->where('status', 'Hadir')->count(),
            'terlambat' => $absenSah->where('status', 'Terlambat')->count(),
            'izin' => $hariIzin->where('leave.request_type', 'Izin')->count(),
            'sakit' => $hariIzin->where('leave.request_type', 'Sakit')->count(),
            'menunggu' => $leaveRequests->where('status', 'Menunggu')->count(),
            // Alfa: hari kerja tanpa absen & tanpa izin, ditambah hari izin yang ditolak.
            'alfa' => collect($riwayat)->where('jenis', 'alfa')->count()
                + $dinasDitolakAlfa
                + collect($riwayat)
                    ->where('jenis', 'izin')
                    ->where('leave.status', 'Ditolak')
                    ->filter(fn ($baris) => $baris['tanggal'] < Carbon::today('Asia/Jakarta')->toDateString())
                    ->count(),
        ];

        return [$user, $attendances, $periode, $leaveRequests, $riwayat, $ringkasan];
    }

    /**
     * Gabungkan absensi dan hari izin/sakit menjadi satu daftar per tanggal.
     * Hari izin hanya ditampilkan untuk Senin–Jumat yang tidak ada absensinya.
     */
    private function gabungRiwayat($attendances, $leaveRequests, Carbon $awal, Carbon $akhir, string $direction = 'desc', ?User $user = null): array
    {
        $baris = [];
        $tanggalAbsen = [];
        $tanggalAbsenSah = [];
        $tanggalIzin = [];

        foreach ($attendances as $attendance) {
            $tanggal = Carbon::parse($attendance->date)->toDateString();
            $tanggalAbsen[$tanggal] = true;

            // Dinas Luar ditolak Mentor tidak menggantikan izin pada tanggal itu.
            if (!$attendance->dinasDitolak()) {
                $tanggalAbsenSah[$tanggal] = true;
            }

            $baris[] = [
                'jenis' => 'absen',
                'tanggal' => $tanggal,
                'attendance' => $attendance,
                'leave' => null,
            ];
        }

        foreach ($leaveRequests as $leave) {
            if (!$leave->start_date || !$leave->end_date) {
                continue;
            }

            $mulai = Carbon::parse($leave->start_date->toDateString(), 'Asia/Jakarta')->max($awal);
            $selesai = Carbon::parse($leave->end_date->toDateString(), 'Asia/Jakarta')->min($akhir);

            for ($hari = $mulai->copy(); $hari->lte($selesai); $hari->addDay()) {
                $tanggal = $hari->toDateString();

                if ($hari->isWeekend() || isset($tanggalAbsenSah[$tanggal])) {
                    continue;
                }

                $tanggalIzin[$tanggal] = true;

                $baris[] = [
                    'jenis' => 'izin',
                    'tanggal' => $tanggal,
                    'attendance' => null,
                    'leave' => $leave,
                ];
            }
        }

        /*
         * Hari kerja dalam periode magang tanpa absen & tanpa pengajuan izin:
         * - hari libur aktif → baris "libur"
         * - hari yang sudah lewat → baris "alfa"
         * Hari ini belum dihitung alfa karena peserta masih bisa absen.
         */
        if ($user && $user->getRawOriginal('start_date') && $user->getRawOriginal('end_date')) {
            $hariIni = Carbon::today('Asia/Jakarta');
            $mulaiMagang = Carbon::parse($user->getRawOriginal('start_date'), 'Asia/Jakarta')->startOfDay();
            $selesaiMagang = Carbon::parse($user->getRawOriginal('end_date'), 'Asia/Jakarta')->startOfDay();

            $dari = $awal->copy()->max($mulaiMagang);
            $sampai = $akhir->copy()->min($selesaiMagang)->min($hariIni);

            if ($dari->lte($sampai)) {
                $libur = Holiday::query()
                    ->active()
                    ->whereDate('date', '>=', $dari->toDateString())
                    ->whereDate('date', '<=', $sampai->toDateString())
                    ->get()
                    ->keyBy(fn (Holiday $holiday) => $holiday->date->toDateString());

                for ($hari = $dari->copy(); $hari->lte($sampai); $hari->addDay()) {
                    $tanggal = $hari->toDateString();

                    if ($hari->isWeekend() || isset($tanggalAbsen[$tanggal]) || isset($tanggalIzin[$tanggal])) {
                        continue;
                    }

                    if ($libur->has($tanggal)) {
                        $baris[] = [
                            'jenis' => 'libur',
                            'tanggal' => $tanggal,
                            'attendance' => null,
                            'leave' => null,
                            'keterangan' => $libur->get($tanggal)->name,
                        ];

                        continue;
                    }

                    if ($tanggal === $hariIni->toDateString()) {
                        continue;
                    }

                    $baris[] = [
                        'jenis' => 'alfa',
                        'tanggal' => $tanggal,
                        'attendance' => null,
                        'leave' => null,
                        'keterangan' => 'Tidak ada absensi maupun pengajuan izin',
                    ];
                }
            }
        }

        usort($baris, fn ($a, $b) => $direction === 'asc'
            ? strcmp($a['tanggal'], $b['tanggal'])
            : strcmp($b['tanggal'], $a['tanggal']));

        return $baris;
    }

    /**
     * Bulan pertama & terakhir periode magang (awal bulan), atau null jika belum diatur.
     */
    private function rentangBulanMagang(User $user): array
    {
        $mulai = $user->getRawOriginal('start_date');
        $selesai = $user->getRawOriginal('end_date');

        if (!$mulai || !$selesai) {
            return [null, null];
        }

        return [
            Carbon::parse($mulai, 'Asia/Jakarta')->startOfMonth(),
            Carbon::parse($selesai, 'Asia/Jakarta')->startOfMonth(),
        ];
    }

    /**
     * Mengambil riwayat absensi berdasarkan bulan.
     */
    private function riwayatBulanIni(Request $request, string $direction = 'asc'): array
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

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

        // Bulan dibatasi ke periode magang peserta (jika periode sudah diatur).
        [$bulanPertama, $bulanTerakhir] = $this->rentangBulanMagang($user);

        if ($bulanPertama && $awal->lt($bulanPertama)) {
            $awal = $bulanPertama->copy();
        }

        if ($bulanTerakhir && $awal->gt($bulanTerakhir)) {
            $awal = $bulanTerakhir->copy();
        }

        $akhir = $awal->copy()->endOfMonth();

        // whereDate agar tanggal terakhir bulan tetap ikut di semua database.
        $attendances = Attendance::with('koreksiPulang')
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $awal->toDateString())
            ->whereDate('date', '<=', $akhir->toDateString())
            ->orderBy('date', $direction)
            ->orderBy('clock_in', $direction)
            ->get();

        return [$user, $attendances, $awal];
    }

    /**
     * Ekspor riwayat absensi ke PDF.
     */
    public function exportPdf(Request $request)
    {
        [$user, $attendances, $periode, $leaveRequests, $riwayat, $ringkasan] =
            $this->riwayatLengkap($request);

        $user->loadMissing(['division', 'location', 'mentor']);

        $pdf = Pdf::loadView(
            'attendance.export-pdf',
            compact('user', 'attendances', 'periode', 'leaveRequests', 'riwayat', 'ringkasan')
        )->setPaper('a4', 'portrait');

        $namaFile = 'riwayat-absensi-'
            . Str::slug($user->name)
            . '-'
            . $periode->format('Y-m')
            . '.pdf';

        return $pdf->download($namaFile);
    }
}