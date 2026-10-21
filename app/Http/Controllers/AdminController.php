<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Division;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /** Dashboard Admin. */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalDivisions = Division::count();
        $totalInterns = User::whereIn('role', ['magang', 'intern'])->count();
        $totalMentors = User::where('role', 'mentor')->count();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalDivisions', 'totalInterns', 'totalMentors'
        ));
    }

    /** Daftar pengguna beserta pilihan divisi. */
    public function users()
    {
        $users = User::with('division')->orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();

        return view('admin.users', compact('users', 'divisions'));
    }

    /** Menetapkan divisi mahasiswa atau mentor. Relasi bimbingan saat ini berdasarkan divisi sama. */
    public function updateUserDivision(Request $request, User $user)
    {
        if (! in_array($user->role, ['magang', 'intern', 'mentor'], true)) {
            return back()->with('error', 'Divisi hanya dapat diatur untuk mahasiswa magang atau mentor.');
        }

        $validated = $request->validate([
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
        ], [
            'division_id.exists' => 'Divisi yang dipilih tidak ditemukan.',
        ]);

        $user->division_id = $validated['division_id'] ?? null;
        $user->save();

        return redirect()->route('admin.users')->with(
            'success', 'Divisi untuk ' . $user->name . ' berhasil diperbarui.'
        );
    }

    /** Menyimpan periode magang mahasiswa selama 3, 4, 5, atau 6 bulan. */
    public function updateInternshipPeriod(Request $request, User $user)
    {
        if (! in_array($user->role, ['magang', 'intern'], true)) {
            return back()->with('error', 'Periode magang hanya dapat diatur untuk mahasiswa magang.');
        }

        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'duration_months' => ['required', 'integer', 'in:3,4,5,6'],
        ], [
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai tidak valid.',
            'duration_months.required' => 'Durasi magang wajib dipilih.',
            'duration_months.integer' => 'Durasi magang tidak valid.',
            'duration_months.in' => 'Durasi magang harus 3, 4, 5, atau 6 bulan.',
        ]);

        $startDate = Carbon::createFromFormat('!Y-m-d', $validated['start_date'], 'Asia/Jakarta');
        $duration = (int) $validated['duration_months'];
        $endDate = $startDate->copy()->addMonthsNoOverflow($duration)->subDay();

        $user->start_date = $startDate->toDateString();
        $user->end_date = $endDate->toDateString();
        $user->save();

        return redirect()->route('admin.users')->with(
            'success',
            'Periode magang ' . $user->name . ' berhasil disimpan selama ' . $duration .
            ' bulan, dari ' . $startDate->format('d-m-Y') . ' sampai ' . $endDate->format('d-m-Y') . '.'
        );
    }

    /** Menampilkan manajemen divisi. */
    public function divisions()
    {
        $divisions = Division::withCount('users')->orderBy('name')->get();
        return view('admin.divisions', compact('divisions'));
    }

    /**
     * Rekap absensi dan logbook dengan filter bulan, divisi, mentor, dan pencarian.
     * Untuk saat ini penugasan mentor mengikuti divisi yang sama karena belum ada mentor_id per mahasiswa.
     */
    public function attendance(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $month = (string) $request->query('month', '');
        $divisionId = $request->query('division_id');
        $mentorId = $request->query('mentor_id');

        $validMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1;
        $validDivisionId = filter_var($divisionId, FILTER_VALIDATE_INT) !== false && $divisionId !== null && $divisionId !== '';
        $validMentorId = filter_var($mentorId, FILTER_VALIDATE_INT) !== false && $mentorId !== null && $mentorId !== '';

        $selectedMentor = $validMentorId
            ? User::where('role', 'mentor')->with('division')->find((int) $mentorId)
            : null;

        $applyFilters = function ($query) use ($search, $validMonth, $month, $validDivisionId, $divisionId, $selectedMentor) {
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

            if ($selectedMentor) {
                if ($selectedMentor->division_id) {
                    $query->whereHas('user', function ($userQuery) use ($selectedMentor) {
                        $userQuery->where('division_id', $selectedMentor->division_id);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            return $query;
        };

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

        $divisions = Division::orderBy('name')->get();
        $mentors = User::where('role', 'mentor')->with('division')->orderBy('name')->get();

        $stats = [
            'total' => $attendances->count(),
            'mahasiswa' => $attendances->pluck('user_id')->unique()->count(),
            'hadir' => $attendances->where('status', 'Hadir')->count(),
            'terlambat' => $attendances->where('status', 'Terlambat')->count(),
            'logbook' => $logbooks->count(),
            'logbook_menunggu' => $logbooks->where('status', 'Menunggu')->count(),
        ];

        return view('admin.attendance', compact(
            'attendances', 'logbooks', 'divisions', 'mentors', 'stats',
            'search', 'month', 'divisionId', 'mentorId'
        ));
    }

    /** Menyajikan selfie hanya untuk pengguna yang lolos middleware Admin/HR pada route. */
    public function attendancePhoto(Attendance $attendance)
    {
        abort_unless($attendance->photo_path, 404, 'Foto absensi tidak tersedia.');
        abort_unless(Storage::disk('public')->exists($attendance->photo_path), 404, 'File foto tidak ditemukan.');

        return Storage::disk('public')->response(
            $attendance->photo_path,
            null,
            ['Cache-Control' => 'private, no-store, max-age=0'],
            'inline'
        );
    }

    /** Menampilkan pengaturan sistem. */
    public function settings()
    {
        return view('admin.settings');
    }
}
