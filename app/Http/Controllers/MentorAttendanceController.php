<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MentorAttendanceController extends Controller
{
    /**
     * Menampilkan pengajuan izin/sakit dan rekap kehadiran
     * mahasiswa yang ditugaskan kepada mentor yang login.
     */
    public function index(Request $request)
    {
        $mentor = $request->user();

        /*
         * FILTER BULAN
         */
        $month = $request->query('month');

        if (
            !is_string($month) ||
            !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)
        ) {
            $month = Carbon::now('Asia/Jakarta')->format('Y-m');
        }

        $period = Carbon::createFromFormat(
            '!Y-m',
            $month,
            'Asia/Jakarta'
        );

        $monthStart = $period->copy()->startOfMonth();
        $monthEnd = $period->copy()->endOfMonth();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        /*
         * Tanggal rekap hanya sampai hari ini.
         * Tanggal mendatang tidak dihitung sebagai Alpa.
         */
        $calculationEnd = $monthEnd->greaterThan($today)
            ? $today->copy()
            : $monthEnd->copy();

        $hasElapsedDays = $monthStart->lte($calculationEnd);

        $monthLabel = $period->copy()
            ->locale('id')
            ->translatedFormat('F Y');

        /*
         * MAHASISWA BIMBINGAN
         *
         * Mentor hanya mendapatkan mahasiswa dengan mentor_id
         * yang sesuai dengan akun mentor yang sedang login.
         */
        $students = User::query()
            ->where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->with('division')
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id');

        $assignmentMissing = $students->isEmpty();

        /*
         * PENGAJUAN IZIN / SAKIT YANG MENUNGGU
         */
        if ($assignmentMissing) {
            $pendingRequests = collect();
        } else {
            $pendingRequests = LeaveRequest::with([
                'user',
            ])
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Menunggu')
                ->orderBy('created_at')
                ->get();
        }

        /*
         * HINDARI QUERY JIKA BELUM ADA MAHASISWA BIMBINGAN.
         */
        if ($assignmentMissing) {
            $attendanceRecords = collect();
            $approvedLeaves = collect();
            $activeHolidays = collect();
        } elseif (!$hasElapsedDays) {
            $attendanceRecords = collect();
            $approvedLeaves = collect();
            $activeHolidays = collect();
        } else {
            /*
             * DATA ABSENSI DALAM PERIODE YANG DIPILIH.
             */
            $attendanceRecords = Attendance::query()
                ->whereIn('user_id', $studentIds)
                ->whereBetween('date', [
                    $monthStart->toDateString(),
                    $calculationEnd->toDateString(),
                ])
                ->whereNotNull('clock_in')
                ->orderBy('date')
                ->get([
                    'id',
                    'user_id',
                    'date',
                    'clock_in',
                    'status',
                ]);

            /*
             * PENGAJUAN IZIN / SAKIT YANG DISETUJUI.
             *
             * Menggunakan irisan rentang tanggal karena satu
             * pengajuan dapat melintasi beberapa bulan.
             */
            $approvedLeaves = LeaveRequest::query()
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Disetujui')
                ->whereDate(
                    'start_date',
                    '<=',
                    $calculationEnd->toDateString()
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $monthStart->toDateString()
                )
                ->orderBy('start_date')
                ->get([
                    'id',
                    'user_id',
                    'request_type',
                    'start_date',
                    'end_date',
                    'status',
                ]);

            /*
             * HARI LIBUR AKTIF.
             */
            $activeHolidays = Holiday::query()
                ->where('is_active', true)
                ->whereBetween('date', [
                    $monthStart->toDateString(),
                    $calculationEnd->toDateString(),
                ])
                ->get(['date']);
        }

        /*
         * KELOMPOKKAN DATA BERDASARKAN MAHASISWA
         * AGAR TIDAK QUERY BERULANG UNTUK SETIAP BARIS.
         */
        $attendanceByStudent = $attendanceRecords->groupBy('user_id');

        $leaveByStudent = $approvedLeaves->groupBy('user_id');

        $holidayDates = $activeHolidays
            ->map(function ($holiday) {
                return Carbon::parse($holiday->date)->toDateString();
            })
            ->flip()
            ->all();

        /*
         * HITUNG REKAP SETIAP MAHASISWA.
         */
        $attendanceSummaries = [];

        foreach ($students as $student) {
            $studentStart = $student->start_date
                ? Carbon::parse($student->start_date)->startOfDay()
                : null;

            $studentEnd = $student->end_date
                ? Carbon::parse($student->end_date)->startOfDay()
                : null;

            $effectiveDays = 0;
            $present = 0;
            $izin = 0;
            $sakit = 0;
            $alpa = 0;

            $validInternshipPeriod =
                $studentStart !== null &&
                $studentEnd !== null &&
                !$studentEnd->lt($studentStart);

            if ($validInternshipPeriod && $hasElapsedDays) {
                $rangeStart = $studentStart->greaterThan($monthStart)
                    ? $studentStart->copy()
                    : $monthStart->copy();

                $rangeEnd = $studentEnd->lessThan($calculationEnd)
                    ? $studentEnd->copy()
                    : $calculationEnd->copy();

                $hasValidRange = $rangeStart->lte($rangeEnd);

                if ($hasValidRange) {
                    /*
                     * Ambil tanggal absensi mahasiswa.
                     * Hadir dan Terlambat sama-sama dianggap hadir.
                     */
                    $studentAttendances = $attendanceByStudent->get(
                        $student->id,
                        collect()
                    );

                    $attendedDates = [];

                    foreach ($studentAttendances as $record) {
                        $date = $record->date?->toDateString();

                        if ($date && $record->clock_in) {
                            $attendedDates[$date] = true;
                        }
                    }

                    /*
                     * Ambil pengajuan yang sudah disetujui.
                     */
                    $studentLeaves = $leaveByStudent->get(
                        $student->id,
                        collect()
                    );

                    /*
                     * HITUNG HARI KERJA EFEKTIF.
                     */
                    $day = $rangeStart->copy();

                    while ($day->lte($rangeEnd)) {
                        $date = $day->toDateString();

                        // Sabtu dan Minggu tidak dihitung.
                        if ($day->isWeekend()) {
                            $day->addDay();
                            continue;
                        }

                        // Hari libur nasional / khusus ESDM.
                        if (isset($holidayDates[$date])) {
                            $day->addDay();
                            continue;
                        }

                        $effectiveDays++;

                        /*
                         * Jika ada absensi masuk, status dihitung hadir.
                         */
                        if (isset($attendedDates[$date])) {
                            $present++;
                            $day->addDay();
                            continue;
                        }

                        /*
                         * Periksa pengajuan yang sudah disetujui
                         * dan mencakup tanggal tersebut.
                         */
                        $leaveOnDate = $studentLeaves->first(
                            function ($leave) use ($date) {
                                if (
                                    !$leave->start_date ||
                                    !$leave->end_date
                                ) {
                                    return false;
                                }

                                return $leave->start_date->toDateString() <= $date
                                    && $leave->end_date->toDateString() >= $date;
                            }
                        );

                        if ($leaveOnDate) {
                            if ($leaveOnDate->request_type === 'Izin') {
                                $izin++;
                            } elseif ($leaveOnDate->request_type === 'Sakit') {
                                $sakit++;
                            } else {
                                $alpa++;
                            }
                        } else {
                            $alpa++;
                        }

                        $day->addDay();
                    }
                }
            }

            /*
             * PERSENTASE KEHADIRAN.
             *
             * Hari izin dan sakit yang disetujui tidak menjadi
             * kewajiban hadir.
             */
            $requiredAttendanceDays = max(
                0,
                $effectiveDays - $izin - $sakit
            );

            $percentage = $requiredAttendanceDays > 0
                ? round(($present / $requiredAttendanceDays) * 100, 1)
                : ($effectiveDays > 0 ? 100 : 0);

            $attendanceSummaries[] = [
                'student' => $student,
                'effective_days' => $effectiveDays,
                'hadir' => $present,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'percentage' => $percentage,
                'valid_period' => $validInternshipPeriod,
            ];
        }

        /*
         * TOTAL REKAP SELURUH MAHASISWA BIMBINGAN.
         */
        $stats = [
            'students' => count($attendanceSummaries),
            'pending_requests' => $pendingRequests->count(),
            'effective_days' => array_sum(
                array_column($attendanceSummaries, 'effective_days')
            ),
            'hadir' => array_sum(
                array_column($attendanceSummaries, 'hadir')
            ),
            'izin' => array_sum(
                array_column($attendanceSummaries, 'izin')
            ),
            'sakit' => array_sum(
                array_column($attendanceSummaries, 'sakit')
            ),
            'alpa' => array_sum(
                array_column($attendanceSummaries, 'alpa')
            ),
        ];

        return view('mentor.attendance', compact(
            'mentor',
            'students',
            'pendingRequests',
            'attendanceSummaries',
            'assignmentMissing',
            'stats',
            'month',
            'monthLabel'
        ));
    }
}
