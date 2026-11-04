<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Division;
use App\Models\Logbook;
use App\Models\Location;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Dashboard Admin.
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalDivisions = Division::count();
        $totalInterns = User::where('role', 'magang')->count();
        $totalMentors = User::where('role', 'mentor')->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalDivisions',
            'totalInterns',
            'totalMentors'
        ));
    }

    /**
     * Daftar pengguna beserta pilihan divisi, mentor, dan lokasi.
     */
    public function users()
    {
        $users = User::with(['division', 'mentor', 'location'])
            ->orderBy('name')
            ->get();

        $divisions = Division::orderBy('name')->get();

        $mentors = User::where('role', 'mentor')
            ->with('division')
            ->orderBy('name')
            ->get();

        $locations = Location::orderBy('name')->get();

        return view('admin.users', compact(
            'users',
            'divisions',
            'mentors',
            'locations'
        ));
    }

    /**
     * Menetapkan divisi mahasiswa atau mentor.
     */
    public function updateUserDivision(Request $request, User $user)
    {
        if (!in_array($user->role, ['magang', 'mentor'], true)) {
            return back()->with(
                'error',
                'Divisi hanya dapat diatur untuk mahasiswa magang atau mentor.'
            );
        }

        $validated = $request->validate([
            'division_id' => [
                'nullable',
                'integer',
                'exists:divisions,id',
            ],
        ], [
            'division_id.exists' => 'Divisi yang dipilih tidak ditemukan.',
        ]);

        $user->division_id = $validated['division_id'] ?? null;
        $user->save();

        return redirect()
            ->route('admin.users')
            ->with(
                'success',
                'Divisi untuk ' . $user->name . ' berhasil diperbarui.'
            );
    }

    /**
     * Menetapkan mentor langsung kepada mahasiswa.
     */
    public function updateUserMentor(Request $request, User $user)
    {
        if ($user->role !== 'magang') {
            return back()->with(
                'error',
                'Penugasan mentor hanya dapat diatur untuk mahasiswa magang.'
            );
        }

        $validated = $request->validate([
            'mentor_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ], [
            'mentor_id.exists' => 'Pengguna yang dipilih tidak ditemukan.',
        ]);

        $mentorId = $validated['mentor_id'] ?? null;

        if (
            $mentorId !== null
            && !User::whereKey($mentorId)
                ->where('role', 'mentor')
                ->exists()
        ) {
            return back()->withErrors([
                'mentor_id' => 'Pengguna yang dipilih bukan akun mentor.',
            ])->withInput();
        }

        $user->mentor_id = $mentorId;
        $user->save();

        $message = $mentorId === null
            ? 'Penugasan mentor untuk ' . $user->name . ' berhasil dihapus.'
            : 'Mentor untuk ' . $user->name . ' berhasil diperbarui.';

        return redirect()
            ->route('admin.users')
            ->with('success', $message);
    }

    /**
     * Menetapkan lokasi kerja mahasiswa magang.
     */
    public function updateUserLocation(Request $request, User $user)
    {
        $role = strtolower(trim((string) $user->role));

        if (!in_array($role, ['magang', 'intern'], true)) {
            return redirect()
                ->route('admin.users')
                ->with(
                    'error',
                    'Lokasi kerja hanya dapat ditetapkan untuk mahasiswa magang.'
                );
        }

        $validated = $request->validate([
            'location_id' => [
                'nullable',
                'integer',
                'exists:locations,id',
            ],
        ], [
            'location_id.exists' => 'Lokasi yang dipilih tidak ditemukan.',
        ]);

        $locationId = !empty($validated['location_id'])
            ? (int) $validated['location_id']
            : null;

        if ($locationId !== null) {
            $location = Location::findOrFail($locationId);

            // Lokasi nonaktif tidak boleh menjadi penempatan baru.
            // Penempatan lama pada lokasi nonaktif tetap dapat dipertahankan.
            if (
                !$location->is_active
                && (int) $user->location_id !== (int) $location->id
            ) {
                return back()->withErrors([
                    'location_id' => 'Pilih lokasi yang masih aktif.',
                ])->withInput();
            }
        }

        $user->location_id = $locationId;
        $user->save();

        return redirect()
            ->route('admin.users')
            ->with(
                'success',
                $locationId === null
                    ? 'Penempatan lokasi ' . $user->name . ' berhasil dihapus.'
                    : 'Lokasi kerja ' . $user->name . ' berhasil diperbarui.'
            );
    }

    /**
     * Mengatur tanggal mulai dan tanggal selesai periode magang.
     */
    public function updateInternshipPeriod(Request $request, User $user)
    {
        $role = strtolower(trim((string) $user->role));

        if (!in_array($role, ['magang', 'intern'], true)) {
            return back()->with(
                'error',
                'Periode magang hanya dapat diatur untuk mahasiswa magang.'
            );
        }

        $validated = $request->validate([
            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
        ], [
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.date_format' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.',
        ]);

        $startDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['start_date'],
            'Asia/Jakarta'
        );

        $endDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['end_date'],
            'Asia/Jakarta'
        );

        $user->start_date = $startDate->toDateString();
        $user->end_date = $endDate->toDateString();
        $user->save();

        return redirect()
            ->route('admin.users')
            ->with(
                'success',
                'Periode magang ' . $user->name
                . ' berhasil disimpan dari '
                . $startDate->format('d-m-Y')
                . ' sampai '
                . $endDate->format('d-m-Y')
                . '.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | MANAJEMEN DIVISI
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan divisi beserta anggota dan jumlah pengguna.
     */
    public function divisions()
    {
        $divisions = Division::with([
            'users' => function ($query) {
                $query->select(
                    'id',
                    'name',
                    'email',
                    'role',
                    'division_id',
                    'nim',
                    'university',
                    'study_program',
                    'mentor_id'
                )
                ->whereIn('role', ['magang', 'mentor'])
                ->orderBy('name');
            },
        ])
            ->withCount('users')
            ->withCount([
                'users as interns_count' => function ($query) {
                    $query->where('role', 'magang');
                },
                'users as mentors_count' => function ($query) {
                    $query->where('role', 'mentor');
                },
            ])
            ->orderBy('name')
            ->get();

        return view('admin.divisions', compact('divisions'));
    }

    /**
     * Menambahkan divisi baru.
     */
    public function storeDivision(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:divisions,name',
            ],
        ], [
            'name.required' => 'Nama divisi wajib diisi.',
            'name.max' => 'Nama divisi maksimal 100 karakter.',
            'name.unique' => 'Nama divisi tersebut sudah terdaftar.',
        ]);

        $division = new Division();
        $division->name = $validated['name'];
        $division->save();

        return redirect()
            ->route('admin.divisions')
            ->with('success', 'Divisi berhasil ditambahkan.');
    }

    /**
     * Mengubah nama divisi.
     */
    public function updateDivision(Request $request, Division $division)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('divisions', 'name')->ignore($division->id),
            ],
        ], [
            'name.required' => 'Nama divisi wajib diisi.',
            'name.max' => 'Nama divisi maksimal 100 karakter.',
            'name.unique' => 'Nama divisi tersebut sudah digunakan.',
        ]);

        $division->name = $validated['name'];
        $division->save();

        return redirect()
            ->route('admin.divisions')
            ->with('success', 'Nama divisi berhasil diperbarui.');
    }

    /**
     * Menghapus divisi hanya jika belum memiliki pengguna.
     */
    public function destroyDivision(Division $division)
    {
        if ($division->users()->exists()) {
            return redirect()
                ->route('admin.divisions')
                ->with(
                    'error',
                    'Divisi tidak dapat dihapus karena masih memiliki pengguna. Pindahkan anggotanyaterlebih dahulu.'
                );
        }

        $division->delete();

        return redirect()
            ->route('admin.divisions')
            ->with('success', 'Divisi berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | REKAP ABSENSI DAN LOGBOOK
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan rekap dengan filter pencarian, bulan, divisi, dan mentor.
     * Termasuk perhitungan hari kerja efektif berdasarkan periode magang
     * serta kalender hari libur aktif.
     */
    public function attendance(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $month = (string) $request->query('month', '');
        $divisionId = $request->query('division_id');
        $mentorId = $request->query('mentor_id');

        $validMonth = preg_match(
            '/^\d{4}-(0[1-9]|1[0-2])$/',
            $month
        ) === 1;

        $validDivisionId = filter_var($divisionId, FILTER_VALIDATE_INT) !== false
            && $divisionId !== null && $divisionId !== '';

        $validMentorId = filter_var($mentorId, FILTER_VALIDATE_INT) !== false
            && $mentorId !== null && $mentorId !== '';

        $selectedMentor = $validMentorId
            ? User::where('role', 'mentor')->with('division')->find((int) $mentorId)
            : null;

        $applyFilters = function ($query) use (
            $search, $validMonth, $month, $validDivisionId,
            $divisionId, $validMentorId, $selectedMentor
        ) {
            if ($search !== '') {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
                });
            }

            if ($validMonth) {
                $period = Carbon::createFromFormat('!Y-m', $month, 'Asia/Jakarta');
                $query->whereBetween('date', [
                    $period->copy()->startOfMonth()->toDateString(),
                    $period->copy()->endOfMonth()->toDateString(),
                ]);
            }

            if ($validDivisionId) {
                $query->whereHas('user', function ($userQuery) use ($divisionId) {
                    $userQuery->where('division_id', (int) $divisionId);
                });
            }

            if ($validMentorId) {
                $query->whereHas('user', function ($userQuery) use ($selectedMentor) {
                    if ($selectedMentor) {
                        $userQuery->where('mentor_id', $selectedMentor->id);
                    } else {
                        $userQuery->whereRaw('1 = 0');
                    }
                });
            }

            return $query;
        };

        // Data absensi dan logbook tetap memakai filter yang sudah ada.
        $attendancesQuery = Attendance::with(['user.division'])
            ->orderByDesc('date')
            ->orderByDesc('clock_in');
        $applyFilters($attendancesQuery);
        $attendances = $attendancesQuery->get();

        $logbooksQuery = Logbook::with(['user.division'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');
        $applyFilters($logbooksQuery);
        $logbooks = $logbooksQuery->get();

        // Pengajuan izin/sakit yang melintasi bulan tetap ditampilkan
        // apabila rentang pengajuannya beririsan dengan bulan laporan.
        $leaveRequestsQuery = LeaveRequest::with([
            'user.division', 'user.mentor', 'reviewer',
        ])->orderByRaw(
            "CASE WHEN status = 'Menunggu' THEN 0 WHEN status = 'Disetujui' THEN 1 ELSE 2 END"
        )->orderByDesc('created_at');

        if ($search !== '') {
            $leaveRequestsQuery->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        if ($validMonth) {
            $leavePeriod = Carbon::createFromFormat('!Y-m', $month, 'Asia/Jakarta');
            $leaveRequestsQuery
                ->whereDate('start_date', '<=', $leavePeriod->copy()->endOfMonth()->toDateString())
                ->whereDate('end_date', '>=', $leavePeriod->copy()->startOfMonth()->toDateString());
        }

        if ($validDivisionId) {
            $leaveRequestsQuery->whereHas('user', function ($userQuery) use ($divisionId) {
                $userQuery->where('division_id', (int) $divisionId);
            });
        }

        if ($validMentorId) {
            $leaveRequestsQuery->whereHas('user', function ($userQuery) use ($selectedMentor) {
                if ($selectedMentor) {
                    $userQuery->where('mentor_id', $selectedMentor->id);
                } else {
                    $userQuery->whereRaw('1 = 0');
                }
            });
        }

        $leaveRequests = $leaveRequestsQuery->get();

        $divisions = Division::orderBy('name')->get();
        $mentors = User::where('role', 'mentor')->with('division')->orderBy('name')->get();

        // Periode laporan: bulan terpilih atau akumulasi masa magang sampai hari ini.
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        if ($validMonth) {
            $selectedPeriod = Carbon::createFromFormat('!Y-m', $month, 'Asia/Jakarta');
            $reportPeriodStart = $selectedPeriod->copy()->startOfMonth();
            $reportPeriodEnd = $selectedPeriod->copy()->endOfMonth();
            if ($reportPeriodEnd->greaterThan($today)) {
                $reportPeriodEnd = $today->copy();
            }
            $workdayPeriodLabel = $selectedPeriod->translatedFormat('F Y');
        } else {
            $reportPeriodStart = null;
            $reportPeriodEnd = $today->copy();
            $workdayPeriodLabel = 'Akumulasi masa magang sampai ' . $today->translatedFormat('d F Y');
        }

        // Mahasiswa yang dihitung harus mengikuti filter laporan yang sama.
        $internsQuery = User::query()
            ->whereIn('role', ['magang', 'intern'])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($search !== '') {
            $internsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        if ($validDivisionId) {
            $internsQuery->where('division_id', (int) $divisionId);
        }
        if ($validMentorId) {
            if ($selectedMentor) {
                $internsQuery->where('mentor_id', $selectedMentor->id);
            } else {
                $internsQuery->whereRaw('1 = 0');
            }
        }

        $interns = $internsQuery->orderBy('name')->get(['id', 'start_date', 'end_date']);

        if (!$validMonth && $interns->isNotEmpty()) {
            $reportPeriodStart = $interns
                ->map(fn ($intern) => Carbon::parse($intern->start_date, 'Asia/Jakarta')->startOfDay())
                ->min()
                ->copy();
        }
        if (!$reportPeriodStart) {
            $reportPeriodStart = $today->copy();
        }

        $workdayStats = [
            'students' => 0,
            'effective_workdays' => 0,
            'attendance_days' => 0,
            'leave_days' => 0,
            'sick_days' => 0,
            'alpha_days' => 0,
            'holiday_dates' => 0,
        ];

        // Batasi setiap mahasiswa pada irisan periode laporan dan periode magangnya.
        $effectiveIntervals = [];
        foreach ($interns as $intern) {
            $studentStart = Carbon::parse($intern->start_date, 'Asia/Jakarta')->startOfDay();
            $studentEnd = Carbon::parse($intern->end_date, 'Asia/Jakarta')->startOfDay();

            if ($studentStart->lessThan($reportPeriodStart)) {
                $studentStart = $reportPeriodStart->copy();
            }
            if ($studentEnd->greaterThan($reportPeriodEnd)) {
                $studentEnd = $reportPeriodEnd->copy();
            }
            if ($studentEnd->lessThan($studentStart)) {
                continue;
            }

            $effectiveIntervals[(int) $intern->id] = [
                'start' => $studentStart,
                'end' => $studentEnd,
            ];
        }

        $workdayStats['students'] = count($effectiveIntervals);
        $studentIds = array_keys($effectiveIntervals);

        // Kalender libur aktif: Sabtu/Minggu dan tanggal ini dikecualikan dari hari kerja.
        $holidayDateSet = collect();
        if ($reportPeriodEnd->greaterThanOrEqualTo($reportPeriodStart)) {
            $holidayDateSet = Holiday::query()
                ->where('is_active', true)
                ->whereBetween('date', [
                    $reportPeriodStart->toDateString(),
                    $reportPeriodEnd->toDateString(),
                ])
                ->pluck('date')
                ->map(fn ($date) => Carbon::parse($date, 'Asia/Jakarta')->toDateString())
                ->flip();
        }
        $workdayStats['holiday_dates'] = $holidayDateSet->count();

        $attendanceDateSet = [];
        $approvedLeaveDateSet = [];

        if (!empty($studentIds) && $reportPeriodEnd->greaterThanOrEqualTo($reportPeriodStart)) {
            $workdayAttendances = Attendance::query()
                ->whereIn('user_id', $studentIds)
                ->whereBetween('date', [
                    $reportPeriodStart->toDateString(),
                    $reportPeriodEnd->toDateString(),
                ])
                ->whereNotNull('clock_in')
                ->get(['user_id', 'date']);

            foreach ($workdayAttendances as $record) {
                $dateString = Carbon::parse($record->date, 'Asia/Jakarta')->toDateString();
                $attendanceDateSet[(int) $record->user_id][$dateString] = true;
            }

            // Hanya pengajuan Disetujui yang mengurangi jumlah alfa.
            $approvedLeaveRequests = LeaveRequest::query()
                ->whereIn('user_id', $studentIds)
                ->where('status', 'Disetujui')
                ->whereDate('start_date', '<=', $reportPeriodEnd->toDateString())
                ->whereDate('end_date', '>=', $reportPeriodStart->toDateString())
                ->get(['user_id', 'request_type', 'start_date', 'end_date']);

            foreach ($approvedLeaveRequests as $leave) {
                $userId = (int) $leave->user_id;
                if (!isset($effectiveIntervals[$userId])) {
                    continue;
                }

                $leaveStart = Carbon::parse($leave->start_date, 'Asia/Jakarta')->startOfDay();
                $leaveEnd = Carbon::parse($leave->end_date, 'Asia/Jakarta')->startOfDay();
                $studentInterval = $effectiveIntervals[$userId];

                if ($leaveStart->lessThan($studentInterval['start'])) {
                    $leaveStart = $studentInterval['start']->copy();
                }
                if ($leaveEnd->greaterThan($studentInterval['end'])) {
                    $leaveEnd = $studentInterval['end']->copy();
                }
                if ($leaveEnd->lessThan($leaveStart)) {
                    continue;
                }

                $requestType = (string) $leave->request_type;
                for ($date = $leaveStart->copy(); $date->lessThanOrEqualTo($leaveEnd); $date->addDay()) {
                    $dateString = $date->toDateString();
                    // Bila dua pengajuan disetujui beririsan, Sakit diprioritaskan.
                    if (!isset($approvedLeaveDateSet[$userId][$dateString]) || $requestType === 'Sakit') {
                        $approvedLeaveDateSet[$userId][$dateString] = $requestType;
                    }
                }
            }
        }

        // Satu hari mahasiswa hanya masuk satu kategori: hadir, izin, sakit, atau alfa.
        foreach ($effectiveIntervals as $userId => $interval) {
            for ($date = $interval['start']->copy(); $date->lessThanOrEqualTo($interval['end']); $date->addDay()) {
                $dateString = $date->toDateString();
                if ($date->isWeekend() || $holidayDateSet->has($dateString)) {
                    continue;
                }

                $workdayStats['effective_workdays']++;
                if (isset($attendanceDateSet[$userId][$dateString])) {
                    $workdayStats['attendance_days']++;
                    continue;
                }

                $approvedType = $approvedLeaveDateSet[$userId][$dateString] ?? null;
                if ($approvedType === 'Sakit') {
                    $workdayStats['sick_days']++;
                } elseif ($approvedType === 'Izin') {
                    $workdayStats['leave_days']++;
                } else {
                    $workdayStats['alpha_days']++;
                }
            }
        }

        $stats = [
            'total' => $attendances->count(),
            'mahasiswa' => $attendances->pluck('user_id')->unique()->count(),
            'hadir' => $attendances->where('status', 'Hadir')->count(),
            'terlambat' => $attendances->where('status', 'Terlambat')->count(),
            'logbook' => $logbooks->count(),
            'logbook_menunggu' => $logbooks->where('status', 'Menunggu')->count(),
            'leave_total' => $leaveRequests->count(),
            'leave_approved' => $leaveRequests->where('status', 'Disetujui')->count(),
            'leave_pending' => $leaveRequests->where('status', 'Menunggu')->count(),
            'leave_rejected' => $leaveRequests->where('status', 'Ditolak')->count(),
            'leave_izin_approved' => $leaveRequests
                ->where('request_type', 'Izin')
                ->where('status', 'Disetujui')
                ->count(),
            'leave_sakit_approved' => $leaveRequests
                ->where('request_type', 'Sakit')
                ->where('status', 'Disetujui')
                ->count(),
        ];

        return view('admin.attendance', compact(
            'attendances',
            'logbooks',
            'leaveRequests',
            'divisions',
            'mentors',
            'stats',
            'workdayStats',
            'workdayPeriodLabel',
            'search',
            'month',
            'divisionId',
            'mentorId'
        ));
    }

    /**
     * Menampilkan foto selfie absensi untuk Admin/HR yang berwenang.
     */
    public function attendancePhoto(Attendance $attendance)
    {
        $path = $attendance->photo_path;

        abort_unless($path, 404, 'Foto absensi tidak tersedia.');

        // Foto baru disimpan di disk local/private. Foto lama mungkin masih di disk public.
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response(
                $path,
                null,
                ['Cache-Control' => 'private, no-store, max-age=0'],
                'inline'
            );
        }

        abort_unless(
            Storage::disk('public')->exists($path),
            404,
            'File foto tidak ditemukan.'
        );

        return Storage::disk('public')->response(
            $path,
            null,
            ['Cache-Control' => 'private, no-store, max-age=0'],
            'inline'
        );
    }

    /**
     * Mempertahankan method pengaturan lama jika masih digunakan.
     * Route utama Pengaturan Sistem menggunakan SystemSettingsController.
     */
    public function settings()
    {
        return view('admin.settings');
    }
}