<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;

/**
 * Daftar "Tugas Hari Ini" untuk peserta magang:
 * absen masuk, absen pulang, logbook harian, logbook ditolak, dan izin menunggu.
 *
 * Setiap item: key, state (done|todo|missed|info), title, description, url, action.
 */
class InternTodayService
{
    public const TZ = 'Asia/Jakarta';

    /** Jam absensi masuk mulai dibuka (sama dengan AttendanceController). */
    private const OPEN_HOUR = 6;

    public function tasks(User $user, ?Carbon $now = null): array
    {
        $now = ($now ?? Carbon::now(self::TZ))->copy()->timezone(self::TZ);
        $today = $now->toDateString();

        // Hanya untuk periode magang yang sedang berjalan.
        if ($user->internshipStatus($today) !== User::INTERNSHIP_ACTIVE) {
            return [];
        }

        $items = [];

        $dayOff = $this->dayOffReason($user, $now);

        if ($dayOff !== null) {
            $items[] = $this->item('day-off', 'info', $dayOff, 'Tidak ada kewajiban absen dan logbook hari ini.');
        } else {
            $items = array_merge($items, $this->attendanceTasks($user, $now));
            $items[] = $this->logbookTodayTask($user, $today);
        }

        // Logbook yang ditolak mentor (tanggal berapa pun) perlu direvisi.
        $ditolak = Logbook::query()
            ->where('user_id', $user->id)
            ->where('status', 'Ditolak')
            ->count();

        if ($ditolak > 0) {
            $items[] = $this->item(
                'logbook-rejected',
                'todo',
                "{$ditolak} logbook ditolak mentor",
                'Baca catatan mentor, perbaiki, lalu kirim ulang.',
                route('logbook'),
                'Revisi Logbook'
            );
        }

        // Pengajuan izin yang belum diproses mentor.
        $izinMenunggu = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'Menunggu')
            ->count();

        if ($izinMenunggu > 0) {
            $items[] = $this->item(
                'leave-pending',
                'info',
                "{$izinMenunggu} pengajuan izin/sakit menunggu persetujuan",
                'Pantau statusnya di Riwayat Izin / Sakit.',
                route('izin.history'),
                'Lihat Status'
            );
        }

        return $items;
    }

    /**
     * Alasan hari ini bukan hari kerja bagi peserta, atau null.
     */
    private function dayOffReason(User $user, Carbon $now): ?string
    {
        if ($now->isWeekend()) {
            return 'Hari ini akhir pekan';
        }

        $holiday = Holiday::activeOn($now->toDateString());

        if ($holiday) {
            return "Hari ini libur: {$holiday->name}";
        }

        $leave = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'Disetujui')
            ->whereDate('start_date', '<=', $now->toDateString())
            ->whereDate('end_date', '>=', $now->toDateString())
            ->first();

        if ($leave) {
            return "Hari ini Anda {$leave->request_type} (disetujui)";
        }

        return null;
    }

    private function attendanceTasks(User $user, Carbon $now): array
    {
        $settings = SystemSetting::current();
        $isFriday = $now->dayOfWeekIso === 5;

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        $batasMasuk = $isFriday ? $settings->friday_clock_in_end : $settings->clock_in_end;
        $jamPulang = $isFriday ? $settings->friday_clock_out_time : $settings->clock_out_time;

        $items = [];

        // ABSEN MASUK
        if ($attendance && $attendance->clock_in) {
            $items[] = $this->item(
                'clock-in',
                'done',
                'Absen masuk pukul ' . $this->jam($attendance->clock_in) . ' WIB',
                $attendance->status === 'Terlambat' ? 'Tercatat terlambat.' : 'Tercatat tepat waktu.'
            );
        } elseif ($now->lt($now->copy()->setTime(self::OPEN_HOUR, 0))) {
            $items[] = $this->item(
                'clock-in',
                'todo',
                'Absen masuk belum dibuka',
                'Absensi masuk dibuka pukul 06.00 WIB. Masuk tepat waktu sebelum '
                    . $this->jam($settings->clock_in_start) . ' WIB.'
            );
        } elseif ($now->lt($now->copy()->setTimeFromTimeString((string) $batasMasuk))) {
            $terlambat = $now->gt($now->copy()->setTimeFromTimeString((string) $settings->clock_in_start));

            $items[] = $this->item(
                'clock-in',
                'todo',
                'Belum absen masuk',
                ($terlambat
                    ? 'Jam masuk ' . $this->jam($settings->clock_in_start) . ' WIB sudah lewat, absensi akan tercatat terlambat. '
                    : 'Absen sebelum ' . $this->jam($settings->clock_in_start) . ' WIB agar tercatat tepat waktu. ')
                    . 'Batas akhir ' . $this->jam($batasMasuk) . ' WIB.',
                route('absen'),
                'Absen Sekarang'
            );
        } else {
            $items[] = $this->item(
                'clock-in',
                'missed',
                'Tidak absen masuk hari ini',
                'Batas absensi masuk pukul ' . $this->jam($batasMasuk)
                    . ' WIB sudah lewat. Jika berhalangan, ajukan izin/sakit.',
                route('izin.create'),
                'Ajukan Izin'
            );

            return $items;
        }

        // ABSEN PULANG (hanya jika sudah absen masuk)
        if ($attendance && $attendance->clock_in) {
            if ($attendance->clock_out) {
                $items[] = $this->item(
                    'clock-out',
                    'done',
                    'Absen pulang pukul ' . $this->jam($attendance->clock_out) . ' WIB',
                    'Terima kasih, kehadiran hari ini lengkap.'
                );
            } elseif ($now->lt($now->copy()->setTimeFromTimeString((string) $jamPulang))) {
                $items[] = $this->item(
                    'clock-out',
                    'todo',
                    'Absen pulang mulai pukul ' . $this->jam($jamPulang) . ' WIB',
                    'Jangan lupa absen pulang sebelum meninggalkan lokasi.'
                );
            } else {
                $items[] = $this->item(
                    'clock-out',
                    'todo',
                    'Belum absen pulang',
                    'Jam pulang sudah tiba. Lakukan absen pulang sekarang.',
                    route('absen'),
                    'Absen Pulang'
                );
            }
        }

        return $items;
    }

    private function logbookTodayTask(User $user, string $today): array
    {
        $logbook = Logbook::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($logbook) {
            return $this->item(
                'logbook-today',
                'done',
                'Logbook hari ini sudah diisi',
                'Status: ' . $logbook->status . '.'
            );
        }

        return $this->item(
            'logbook-today',
            'todo',
            'Logbook hari ini belum diisi',
            'Catat kegiatan hari ini sebelum lupa.',
            route('logbook'),
            'Isi Logbook'
        );
    }

    private function item(
        string $key,
        string $state,
        string $title,
        string $description,
        ?string $url = null,
        ?string $action = null
    ): array {
        return compact('key', 'state', 'title', 'description', 'url', 'action');
    }

    private function jam($nilai): string
    {
        return str_replace(':', '.', substr((string) $nilai, 0, 5));
    }
}
