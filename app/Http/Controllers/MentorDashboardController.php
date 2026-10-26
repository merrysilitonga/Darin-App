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
    /**
     * Menampilkan dashboard mentor beserta mahasiswa bimbingannya.
     */
    public function index()
    {
        $mentor = auth()->user();

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        /*
        |--------------------------------------------------------------------------
        | INFORMASI DIVISI MENTOR
        |--------------------------------------------------------------------------
        */

        $division = $mentor->division_id
            ? Division::find($mentor->division_id)
            : null;

        $divisionName = $division?->name ?? 'Belum ditetapkan';

        /*
        |--------------------------------------------------------------------------
        | DAFTAR MAHASISWA BIMBINGAN
        |--------------------------------------------------------------------------
        */

        $students = User::where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->with('division')
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id');

        $totalInterns = $students->count();

        $assignmentMissing = $studentIds->isEmpty();

        /*
        |--------------------------------------------------------------------------
        | INISIALISASI STATISTIK
        |--------------------------------------------------------------------------
        */

        $presentToday = 0;
        $approvedLeaveToday = 0;
        $pendingLogbooksCount = 0;
        $pendingLeaveRequestsCount = 0;

        $pendingLogbooks = collect();
        $pendingLeaves = collect();

        /*
        |--------------------------------------------------------------------------
        | STATISTIK DAN NOTIFIKASI MAHASISWA
        |--------------------------------------------------------------------------
        */

        if ($studentIds->isNotEmpty()) {
            // Mahasiswa yang sudah melakukan absensi hari ini.
            $presentToday = Attendance::whereIn(
                'user_id',
                $studentIds
            )
                ->whereDate('date', $today)
                ->whereIn('status', ['Hadir', 'Terlambat'])
                ->distinct()
                ->count('user_id');

            // Mahasiswa yang sedang menjalani izin yang disetujui.
            $approvedLeaveToday = LeaveRequest::whereIn(
                'user_id',
                $studentIds
            )
                ->where('status', 'Disetujui')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct()
                ->count('user_id');

            // Jumlah logbook yang menunggu pemeriksaan.
            $pendingLogbooksCount = Logbook::whereIn(
                'user_id',
                $studentIds
            )
                ->where('status', 'Menunggu')
                ->count();

            // Jumlah pengajuan izin yang menunggu pemeriksaan.
            $pendingLeaveRequestsCount = LeaveRequest::whereIn(
                'user_id',
                $studentIds
            )
                ->where('status', 'Menunggu')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | NOTIFIKASI LOGBOOK
            |--------------------------------------------------------------------------
            */

            $pendingLogbooks = Logbook::with('user:id,name')
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu')
                ->latest('created_at')
                ->get()
                ->map(function ($logbook) {
                    return [
                        'student_name' => $logbook->user?->name
                            ?? 'Mahasiswa',
                        'type' => 'Logbook Harian',
                        'description' => $logbook->topic
                            ?: Str::limit(
                                strip_tags(
                                    $logbook->description ?? ''
                                ),
                                120
                            ),
                        'created_at' => $logbook->created_at,
                        'url' => route('mentor.logbook'),
                        'icon' => '📝',
                        'badge_class' => 'bg-amber-50 text-amber-700',
                    ];
                });

            /*
            |--------------------------------------------------------------------------
            | NOTIFIKASI PENGAJUAN IZIN
            |--------------------------------------------------------------------------
            */

            $pendingLeaves = LeaveRequest::with('user:id,name')
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu')
                ->latest('created_at')
                ->get()
                ->map(function ($leave) {
                    return [
                        'student_name' => $leave->user?->name
                            ?? 'Mahasiswa',
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

        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN NOTIFIKASI
        |--------------------------------------------------------------------------
        */

        $notifications = $pendingLogbooks
            ->concat($pendingLeaves)
            ->sortByDesc(function ($item) {
                return $item['created_at']?->timestamp ?? 0;
            })
            ->take(8)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RINGKASAN DASHBOARD
        |--------------------------------------------------------------------------
        */

        $pendingActionCount = $pendingLogbooksCount
            + $pendingLeaveRequestsCount;

        $attendancePercent = $totalInterns > 0
            ? (int) round(
                ($presentToday / $totalInterns) * 100
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | KIRIM DATA KE VIEW
        |--------------------------------------------------------------------------
        */

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