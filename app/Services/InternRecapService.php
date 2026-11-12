<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use Carbon\Carbon;

/**
 * Menyusun rekap kehadiran & logbook satu peserta selama periode magang.
 *
 * Aturan per hari (sama dengan rekap admin):
 * - Sabtu/Minggu dan hari libur aktif tidak dihitung sebagai hari kerja.
 * - Ada absen masuk → Hadir / Terlambat.
 * - Tidak absen tetapi ada izin/sakit Disetujui → Izin / Sakit (Sakit diprioritaskan).
 * - Selain itu → Alfa. Hari ini yang belum absen → "Belum Absen" (tidak dihitung).
 */
class InternRecapService
{
    public const TZ = 'Asia/Jakarta';

    public function build(User $intern): array
    {
        $today = Carbon::today(self::TZ);

        $intern->loadMissing(['division', 'mentor']);

        $start = $this->rawDate($intern, 'start_date');
        $end = $this->rawDate($intern, 'end_date');

        $summary = [
            'effective_workdays' => 0,
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alfa' => 0,
            'libur' => 0,
            'attendance_rate' => 0,
        ];

        $base = [
            'intern' => $intern,
            'start' => $start,
            'end' => $end,
            'countUntil' => null,
            'periodState' => 'Belum diatur',
            'summary' => $summary,
            'days' => [],
            'logbookStats' => ['total' => 0, 'Disetujui' => 0, 'Menunggu' => 0, 'Ditolak' => 0, 'Draft' => 0],
            'leaveRequests' => collect(),
            'generatedAt' => Carbon::now(self::TZ),
        ];

        if (!$start || !$end || $end->lt($start)) {
            return $base;
        }

        if ($today->lt($start)) {
            $base['periodState'] = 'Belum dimulai';

            return $base;
        }

        $countUntil = $today->gt($end) ? $end->copy() : $today->copy();
        $base['countUntil'] = $countUntil;
        $base['periodState'] = $today->gt($end) ? 'Selesai' : 'Berlangsung';

        $range = [$start->toDateString(), $countUntil->toDateString()];

        // Hari libur aktif.
        $holidays = Holiday::query()
            ->active()
            ->whereDate('date', '>=', $range[0])
            ->whereDate('date', '<=', $range[1])
            ->get()
            ->keyBy(fn (Holiday $holiday) => $holiday->date->toDateString());

        // Absensi.
        $attendances = Attendance::query()
            ->where('user_id', $intern->id)
            ->whereDate('date', '>=', $range[0])
            ->whereDate('date', '<=', $range[1])
            ->whereNotNull('clock_in')
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->date->toDateString());

        // Logbook per tanggal.
        $logbooks = Logbook::query()
            ->where('user_id', $intern->id)
            ->whereDate('date', '>=', $range[0])
            ->whereDate('date', '<=', $range[1])
            ->get()
            ->keyBy(fn (Logbook $logbook) => $logbook->date->toDateString());

        // Seluruh pengajuan izin pada periode magang.
        $leaveRequests = LeaveRequest::query()
            ->where('user_id', $intern->id)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->orderBy('start_date')
            ->get();

        $approvedLeaveDates = [];

        foreach ($leaveRequests->where('status', 'Disetujui') as $leave) {
            $leaveStart = $leave->start_date->copy()->max($start);
            $leaveEnd = $leave->end_date->copy()->min($countUntil);

            for ($date = $leaveStart->copy(); $date->lte($leaveEnd); $date->addDay()) {
                $key = $date->toDateString();

                if (!isset($approvedLeaveDates[$key]) || $leave->request_type === 'Sakit') {
                    $approvedLeaveDates[$key] = (string) $leave->request_type;
                }
            }
        }

        $days = [];

        for ($date = $start->copy(); $date->lte($countUntil); $date->addDay()) {
            $key = $date->toDateString();
            $attendance = $attendances->get($key);
            $holiday = $holidays->get($key);

            $row = [
                'date' => $date->copy(),
                'status' => null,
                'clock_in' => $attendance?->clock_in,
                'clock_out' => $attendance?->clock_out,
                'work_location' => $attendance?->work_location,
                'note' => null,
                'logbook' => $logbooks->get($key),
            ];

            if ($date->isWeekend()) {
                // Akhir pekan tidak ditampilkan kecuali ada absensi/logbook.
                if ($attendance || $row['logbook']) {
                    $row['status'] = 'Akhir Pekan';
                    $days[] = $row;
                }

                continue;
            }

            if ($holiday) {
                $summary['libur']++;
                $row['status'] = 'Libur';
                $row['note'] = $holiday->name;
                $days[] = $row;

                continue;
            }

            if ($attendance) {
                $summary['effective_workdays']++;
                $isLate = $attendance->status === 'Terlambat';
                $summary[$isLate ? 'terlambat' : 'hadir']++;
                $row['status'] = $isLate ? 'Terlambat' : 'Hadir';
                $days[] = $row;

                continue;
            }

            $leaveType = $approvedLeaveDates[$key] ?? null;

            if ($leaveType === 'Sakit' || $leaveType === 'Izin') {
                $summary['effective_workdays']++;
                $summary[$leaveType === 'Sakit' ? 'sakit' : 'izin']++;
                $row['status'] = $leaveType;
                $days[] = $row;

                continue;
            }

            if ($key === $today->toDateString()) {
                $row['status'] = 'Belum Absen';
                $days[] = $row;

                continue;
            }

            $summary['effective_workdays']++;
            $summary['alfa']++;
            $row['status'] = 'Alfa';
            $days[] = $row;
        }

        $summary['attendance_rate'] = $summary['effective_workdays'] > 0
            ? (int) round((($summary['hadir'] + $summary['terlambat']) / $summary['effective_workdays']) * 100)
            : 0;

        // Statistik logbook seluruh periode.
        $logbookCounts = Logbook::query()
            ->where('user_id', $intern->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $logbookStats = [
            'total' => (int) $logbookCounts->sum(),
            'Disetujui' => (int) ($logbookCounts['Disetujui'] ?? 0),
            'Menunggu' => (int) ($logbookCounts['Menunggu'] ?? 0),
            'Ditolak' => (int) ($logbookCounts['Ditolak'] ?? 0),
            'Draft' => (int) ($logbookCounts['Draft'] ?? 0),
        ];

        return array_merge($base, [
            'summary' => $summary,
            'days' => $days,
            'logbookStats' => $logbookStats,
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Tanggal mentah dari kolom user, diparse di zona waktu WIB.
     */
    private function rawDate(User $user, string $column): ?Carbon
    {
        $raw = $user->getRawOriginal($column);

        return $raw ? Carbon::parse($raw, self::TZ)->startOfDay() : null;
    }
}
