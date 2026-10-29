<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Division;
use App\Models\Logbook;
use App\Models\Location;
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
                    'Divisi tidak dapat dihapus karena masih memiliki pengguna. Pindahkan anggotanya terlebih dahulu.'
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

        $validDivisionId = filter_var(
            $divisionId,
            FILTER_VALIDATE_INT
        ) !== false
            && $divisionId !== null
            && $divisionId !== '';

        $validMentorId = filter_var(
            $mentorId,
            FILTER_VALIDATE_INT
        ) !== false
            && $mentorId !== null
            && $mentorId !== '';

        $selectedMentor = $validMentorId
            ? User::where('role', 'mentor')
                ->with('division')
                ->find((int) $mentorId)
            : null;

        $applyFilters = function ($query) use (
            $search,
            $validMonth,
            $month,
            $validDivisionId,
            $divisionId,
            $validMentorId,
            $selectedMentor
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
                $period = Carbon::createFromFormat(
                    '!Y-m',
                    $month,
                    'Asia/Jakarta'
                );

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

        $mentors = User::where('role', 'mentor')
            ->with('division')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $attendances->count(),
            'mahasiswa' => $attendances->pluck('user_id')->unique()->count(),
            'hadir' => $attendances->where('status', 'Hadir')->count(),
            'terlambat' => $attendances->where('status', 'Terlambat')->count(),
            'logbook' => $logbooks->count(),
            'logbook_menunggu' => $logbooks->where('status', 'Menunggu')->count(),
        ];

        return view('admin.attendance', compact(
            'attendances',
            'logbooks',
            'divisions',
            'mentors',
            'stats',
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