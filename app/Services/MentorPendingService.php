<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use Carbon\Carbon;

/**
 * Ringkasan tindakan mentor yang masih menunggu (logbook & izin berstatus "Menunggu").
 * Didaftarkan sebagai scoped service: dihitung sekali per request.
 */
class MentorPendingService
{
    /** Batas hari sebelum item dianggap tertunda terlalu lama. */
    public const OVERDUE_DAYS = 3;

    private array $cache = [];

    public function summary(User $mentor): array
    {
        if (isset($this->cache[$mentor->id])) {
            return $this->cache[$mentor->id];
        }

        $studentIds = User::query()
            ->where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->pluck('id');

        $summary = [
            'logbook' => 0,
            'leave' => 0,
            'total' => 0,
            'overdue' => 0,
        ];

        if ($studentIds->isNotEmpty()) {
            $batas = Carbon::now()->subDays(self::OVERDUE_DAYS);

            $logbookQuery = Logbook::query()
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu');

            $leaveQuery = LeaveRequest::query()
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu');

            $summary['logbook'] = (clone $logbookQuery)->count();
            $summary['leave'] = (clone $leaveQuery)->count();
            $summary['total'] = $summary['logbook'] + $summary['leave'];

            $summary['overdue'] = (clone $logbookQuery)
                ->where(function ($query) use ($batas) {
                    $query->where('submitted_at', '<=', $batas)
                        ->orWhere(function ($subQuery) use ($batas) {
                            $subQuery->whereNull('submitted_at')
                                ->where('created_at', '<=', $batas);
                        });
                })
                ->count()
                + (clone $leaveQuery)
                    ->where('created_at', '<=', $batas)
                    ->count();
        }

        return $this->cache[$mentor->id] = $summary;
    }

    /**
     * Label lama menunggu, mis. "Hari ini", "1 hari", "5 hari".
     */
    public static function waitingDays(?Carbon $since): int
    {
        if (!$since) {
            return 0;
        }

        return (int) max(
            0,
            $since->copy()->startOfDay()->diffInDays(Carbon::now()->startOfDay())
        );
    }
}
