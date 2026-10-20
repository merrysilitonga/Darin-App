<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Division;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MentorDashboardController extends Controller
{
    public function index()
    {
        $mentor = auth()->user();

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $assignmentMissing = empty($mentor->division_id);

        $division = $mentor->division_id
            ? Division::find($mentor->division_id)
            : null;

        $divisionName = $division?->name ?? 'Belum ditetapkan';

        $students = $assignmentMissing
            ? collect()
            : User::whereIn('role', ['magang', 'intern'])
                ->where('division_id', $mentor->division_id)
                ->orderBy('name')
                ->get();

        $studentIds = $students->pluck('id');

        $totalInterns = $students->count();
        $presentToday = 0;
        $approvedLeaveToday = 0;
        $pendingLogbooksCount = 0;
        $pendingLeaveRequestsCount = 0;

        $pendingLogbooks = collect();
        $pendingLeaves = collect();

        if ($studentIds->isNotEmpty()) {
            $presentToday = Attendance::whereIn(
                'user_id',
                $studentIds
            )
                ->whereDate('date', $today)
                ->whereIn('status', ['Hadir', 'Terlambat'])
                ->distinct()
                ->count('user_id');

            $approvedLeaveToday = LeaveRequest::whereIn(
                'user_id',
                $studentIds
            )
                ->where('status', 'Disetujui')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct()
                ->count('user_id');

            $pendingLogbooksCount = Logbook::whereIn(
                'user_id',
                $studentIds
            )
                ->where('status', 'Menunggu')
                ->count();

            $pendingLeaveRequestsCount = LeaveRequest::whereIn(
                'user_id',
                $studentIds
            )
                ->where('status', 'Menunggu')
                ->count();

            $pendingLogbooks = Logbook::with('user:id,name')
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu')
                ->latest('created_at')
                ->get()
                ->map(function ($logbook) {
                    return [
                        'student_name' => $logbook->user?->name ?? 'Mahasiswa',
                        'type' => 'Logbook Harian',
                        'description' => $logbook->topic
                            ?: Str::limit(
                                strip_tags($logbook->description ?? ''),
                                120
                            ),
                        'created_at' => $logbook->created_at,
                        'url' => route('mentor.logbook'),
                        'icon' => '📝',
                        'badge_class' => 'bg-amber-50 text-amber-700',
                    ];
                });

            $pendingLeaves = LeaveRequest::with('user:id,name')
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu')
                ->latest('created_at')
                ->get()
                ->map(function ($leave) {
                    return [
                        'student_name' => $leave->user?->name ?? 'Mahasiswa',
                        'type' => 'Pengajuan ' . $leave->request_type,
                        'description' => Str::limit(
                            strip_tags($leave->reason ?? ''),
                            120
                        ),
                        'created_at' => $leave->created_at,
                        'url' => route('mentor.leave-requests'),
                        'icon' => '📋',
                        'badge_class' => 'bg-blue-50 text-blue-700',
                    ];
                });
        }

        $notifications = $pendingLogbooks
            ->concat($pendingLeaves)
            ->sortByDesc(function ($item) {
                return $item['created_at']?->timestamp ?? 0;
            })
            ->take(8)
            ->values();

        $pendingActionCount = $pendingLogbooksCount
            + $pendingLeaveRequestsCount;

        $attendancePercent = $totalInterns > 0
            ? (int) round(($presentToday / $totalInterns) * 100)
            : 0;

        return view('mentor.dashboard', compact(
            'mentor',
            'students',
            'totalInterns',
            'presentToday',
            'approvedLeaveToday',
            'pendingLogbooksCount',
            'pendingLeaveRequestsCount',
            'pendingActionCount',
            'attendancePercent',
            'notifications',
            'assignmentMissing',
            'divisionName'
        ));
    }
}