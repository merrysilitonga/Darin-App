<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\Logbook;

class AdminController extends Controller
{
    /**
     * Dashboard Admin.
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalDivisions = Division::count();

        $totalInterns = User::whereIn('role', [
            'magang',
            'intern',
        ])->count();

        $totalMentors = User::where('role', 'mentor')->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalDivisions',
            'totalInterns',
            'totalMentors'
        ));
    }

    /**
     * Menampilkan daftar pengguna.
     */
    public function users()
    {
        $users = User::with('division')
            ->orderBy('name')
            ->get();

        return view('admin.users', compact('users'));
    }

    /**
     * Menyimpan periode magang mahasiswa.
     * Durasi yang diperbolehkan: 3, 4, 5, atau 6 bulan.
     */
    public function updateInternshipPeriod(Request $request, User $user)
    {
        // Periode magang hanya bisa diatur untuk mahasiswa.
        if (!in_array($user->role, ['magang', 'intern'], true)) {
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
            'duration_months' => [
                'required',
                'integer',
                'in:3,4,5,6',
            ],
        ], [
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai tidak valid.',
            'duration_months.required' => 'Durasi magang wajib dipilih.',
            'duration_months.integer' => 'Durasi magang tidak valid.',
            'duration_months.in' => 'Durasi magang harus 3, 4, 5, atau 6 bulan.',
        ]);

        $startDate = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['start_date'],
            'Asia/Jakarta'
        );

        $duration = (int) $validated['duration_months'];

        // Hitung tanggal selesai tanpa melampaui akhir bulan.
        // Contoh: mulai 31 Januari, penambahan bulan menyesuaikan
        // tanggal yang tersedia di bulan tujuan.
        $endDate = $startDate->copy()
            ->addMonthsNoOverflow($duration)
            ->subDay();

        $user->start_date = $startDate->toDateString();
        $user->end_date = $endDate->toDateString();
        $user->save();

        return redirect()
            ->route('admin.users')
            ->with(
                'success',
                'Periode magang ' . $user->name .
                ' berhasil disimpan selama ' . $duration .
                ' bulan, dari ' .
                $startDate->format('d-m-Y') . ' sampai ' .
                $endDate->format('d-m-Y') . '.'
            );
    }

    /**
     * Menampilkan manajemen divisi.
     */
    public function divisions()
    {
        $divisions = Division::withCount('users')->get();

        return view('admin.divisions', compact('divisions'));
    }

    /**
     * Menampilkan rekap absensi seluruh pengguna.
     */
    public function attendance()
    {
        $attendances = Attendance::with('user.division')
            ->latest()
            ->get();

        return view('admin.attendance', compact('attendances'));
    }

    /**
     * Menampilkan pengaturan sistem.
     */
    public function settings()
    {
        return view('admin.settings');
    }
}
