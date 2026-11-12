<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Data grafik dashboard admin:
 * - Ringkasan kehadiran hari ini.
 * - Tren kehadiran harian N hari terakhir (tepat waktu, terlambat, izin/sakit, alfa).
 * - Persentase kehadiran per divisi bulan berjalan.
 *
 * Aturan hari kerja sama dengan rekap: Sabtu/Minggu dan libur aktif tidak dihitung,
 * hanya peserta yang periode magangnya mencakup tanggal tersebut.
 */
class AdminChartService
{
    public const TZ = 'Asia/Jakarta';

    public function build(int $days = 30): array
    {
        $today = Carbon::today(self::TZ);
        $from = $today->copy()->subDays($days - 1);
        $monthStart = $today->copy()->startOfMonth();
        $rangeStart = $from->lt($monthStart) ? $from->copy() : $monthStart->copy();

        $interns = User::query()
            ->with('division:id,name')
            ->where('role', 'magang')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $rangeStart->toDateString())
            ->get(['id', 'name', 'division_id', 'start_date', 'end_date']);

        $internIds = $interns->pluck('id');

        $holidays = Holiday::query()
            ->active()
            ->whereDate('date', '>=', $rangeStart->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->get()
            ->mapWithKeys(fn (Holiday $holiday) => [$holiday->date->toDateString() => $holiday->name]);

        // [tanggal][user_id] => status absensi.
        $attendanceMap = [];

        Attendance::query()
            ->whereIn('user_id', $internIds)
            ->whereNotNull('clock_in')
            ->whereDate('date', '>=', $rangeStart->toDateString())
            ->whereDate('date', '<=', $today->toDateString())
            ->get(['user_id', 'date', 'status'])
            ->each(function (Attendance $attendance) use (&$attendanceMap) {
                $attendanceMap[$attendance->date->toDateString()][$attendance->user_id] = $attendance->status;
            });

        // [tanggal][user_id] => true untuk izin/sakit disetujui.
        $leaveMap = [];

        LeaveRequest::query()
            ->whereIn('user_id', $internIds)
            ->where('status', 'Disetujui')
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $rangeStart->toDateString())
            ->get(['user_id', 'start_date', 'end_date'])
            ->each(function (LeaveRequest $leave) use (&$leaveMap, $rangeStart, $today) {
                $mulai = $leave->start_date->copy()->max($rangeStart);
                $selesai = $leave->end_date->copy()->min($today);

                foreach (CarbonPeriod::create($mulai, $selesai) as $tanggal) {
                    $leaveMap[$tanggal->toDateString()][$leave->user_id] = true;
                }
            });

        $periods = $interns->mapWithKeys(fn (User $intern) => [
            $intern->id => [
                Carbon::parse($intern->getRawOriginal('start_date'), self::TZ)->toDateString(),
                Carbon::parse($intern->getRawOriginal('end_date'), self::TZ)->toDateString(),
            ],
        ]);

        $trend = [];

        foreach (CarbonPeriod::create($from, $today) as $tanggal) {
            $trend[] = $this->dayStats($tanggal, $today, $periods, $holidays, $attendanceMap, $leaveMap);
        }

        $todayStats = end($trend);

        return [
            'today' => $todayStats,
            'trend' => $trend,
            'trendMax' => max(1, ...array_map(fn ($day) => $day['active'], $trend)),
            'divisions' => $this->divisionRates($interns, $periods, $monthStart, $today, $holidays, $attendanceMap),
            'monthLabel' => $today->locale('id')->translatedFormat('F Y'),
            'days' => $days,
        ];
    }

    private function dayStats(
        Carbon $tanggal,
        Carbon $today,
        Collection $periods,
        Collection $holidays,
        array $attendanceMap,
        array $leaveMap
    ): array {
        $key = $tanggal->toDateString();
        $isToday = $key === $today->toDateString();

        $stats = [
            'date' => $tanggal->copy(),
            'is_today' => $isToday,
            'off' => null,
            'active' => 0,
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'alfa' => 0,
            'belum' => 0,
            'rate' => null,
        ];

        if ($tanggal->isWeekend()) {
            $stats['off'] = 'Akhir pekan';

            return $stats;
        }

        if ($holidays->has($key)) {
            $stats['off'] = 'Libur: ' . $holidays->get($key);

            return $stats;
        }

        foreach ($periods as $userId => [$mulai, $selesai]) {
            if ($key < $mulai || $key > $selesai) {
                continue;
            }

            $stats['active']++;

            $status = $attendanceMap[$key][$userId] ?? null;

            if ($status !== null) {
                $stats[$status === 'Terlambat' ? 'terlambat' : 'hadir']++;
            } elseif (isset($leaveMap[$key][$userId])) {
                $stats['izin']++;
            } elseif ($isToday) {
                $stats['belum']++;
            } else {
                $stats['alfa']++;
            }
        }

        $dasar = $stats['active'] - $stats['belum'];

        if ($dasar > 0) {
            $stats['rate'] = (int) round((($stats['hadir'] + $stats['terlambat']) / $dasar) * 100);
        }

        return $stats;
    }

    /**
     * Persentase kehadiran per divisi bulan berjalan.
     * Hari ini hanya dihitung bagi peserta yang sudah absen.
     */
    private function divisionRates(
        Collection $interns,
        Collection $periods,
        Carbon $monthStart,
        Carbon $today,
        Collection $holidays,
        array $attendanceMap
    ): array {
        $hasil = [];

        foreach ($interns as $intern) {
            [$mulai, $selesai] = $periods[$intern->id];
            $nama = $intern->division?->name ?? 'Tanpa Divisi';

            $hasil[$nama] ??= ['name' => $nama, 'interns' => 0, 'workdays' => 0, 'present' => 0];
            $terhitung = false;

            foreach (CarbonPeriod::create($monthStart, $today) as $tanggal) {
                $key = $tanggal->toDateString();

                if ($key < $mulai || $key > $selesai || $tanggal->isWeekend() || $holidays->has($key)) {
                    continue;
                }

                $hadir = isset($attendanceMap[$key][$intern->id]);

                if ($key === $today->toDateString() && !$hadir) {
                    continue;
                }

                $terhitung = true;
                $hasil[$nama]['workdays']++;
                $hasil[$nama]['present'] += $hadir ? 1 : 0;
            }

            if ($terhitung) {
                $hasil[$nama]['interns']++;
            }
        }

        $hasil = array_values(array_filter($hasil, fn ($row) => $row['workdays'] > 0));

        foreach ($hasil as &$row) {
            $row['rate'] = (int) round(($row['present'] / $row['workdays']) * 100);
        }
        unset($row);

        usort($hasil, fn ($a, $b) => $b['rate'] <=> $a['rate'] ?: strcmp($a['name'], $b['name']));

        return $hasil;
    }
}
