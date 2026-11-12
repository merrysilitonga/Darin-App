<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

use App\Models\User;

use App\Http\Controllers\AdminLocationController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AdminFeedbackController;
use App\Http\Controllers\InternRecapController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\MentorLogbookController;
use App\Http\Controllers\MentorLeaveRequestController;
use App\Http\Controllers\MentorDashboardController;
use App\Http\Controllers\MentorAttendanceController;

use App\Http\Middleware\EnsureInternshipActive;
use App\Http\Middleware\RoleMiddleware;


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');


/*
|--------------------------------------------------------------------------
| REGISTRASI MAHASISWA
|--------------------------------------------------------------------------
| Registrasi publik hanya membuat akun mahasiswa.
| Role ditentukan oleh AuthController, bukan input formulir.
*/

Route::get('/register', [AuthController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [AuthController::class, 'processRegister'])
    ->middleware('guest');


/*
|--------------------------------------------------------------------------
| PENDAFTARAN MENTOR
|--------------------------------------------------------------------------
| Hanya Admin/HR yang sudah login boleh membuat akun Mentor.
*/

Route::middleware([
    'auth',
    RoleMiddleware::class . ':admin,hr',
])->group(function () {

    Route::get(
        '/register/mentor',
        [AuthController::class, 'showMentorRegister']
    )->name('mentor.register');

    Route::post(
        '/register/mentor',
        [AuthController::class, 'processMentorRegister']
    )->name('mentor.register.process');

});


/*
|--------------------------------------------------------------------------
| LUPA PASSWORD
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {

    $request->validate([
        'email' => ['required', 'email'],
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
        return back()->with(
            'status',
            'Jika email terdaftar, link reset password akan dikirim. Periksa kotak masuk atau folder Spam.'
        );
    }

    return back()->withErrors([
        'email' => 'Link reset belum dapat dikirim. Periksa konfigurasi email aplikasi.',
    ]);

})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (
    Request $request,
    string $token
) {
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->query('email', ''),
    ]);

})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {

    $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
        ],
    ]);

    $status = Password::reset(
        $request->only(
            'email',
            'password',
            'password_confirmation',
            'token'
        ),
        function (User $user, string $password) {

            $user->forceFill([
                'password' => Hash::make($password),
            ]);

            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return redirect()
            ->route('login')
            ->with(
                'status',
                'Password berhasil diubah. Silakan login menggunakan password baru.'
            );
    }

    return back()->withErrors([
        'email' => 'Link reset tidak valid atau sudah kedaluwarsa. Silakan minta link baru.',
    ]);

})->middleware('guest')->name('password.update');


/*
|--------------------------------------------------------------------------
| ROUTE UMUM PENGGUNA LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profil');

    Route::put('/profil', [ProfileController::class, 'update'])
        ->name('profil.update');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});


/*
|--------------------------------------------------------------------------
| ROUTE MAHASISWA MAGANG
|--------------------------------------------------------------------------
| Standar role mahasiswa hanya "magang".
*/

Route::middleware([
    'auth',
    RoleMiddleware::class . ':magang',
    // Setelah periode magang berakhir, akun otomatis menjadi baca-saja.
    EnsureInternshipActive::class,
])->group(function () {

    Route::get(
        '/dashboard-magang',
        [DashboardController::class, 'index']
    )->name('dashboard.magang');


    // Absensi.

    Route::get(
        '/absen',
        [AttendanceController::class, 'index']
    )->name('absen');

    Route::post(
        '/absen',
        [AttendanceController::class, 'storeClockIn']
    )->name('absen.store');

    Route::post(
        '/absen-pulang',
        [AttendanceController::class, 'storeClockOut']
    )->name('absen.checkout');


    // Rekap magang milik sendiri.

    Route::get(
        '/rekap-saya',
        [InternRecapController::class, 'mine']
    )->name('rekap.saya');

    Route::get(
        '/rekap-saya/pdf',
        [InternRecapController::class, 'minePdf']
    )->name('rekap.saya.pdf');


    // Saran & masukan.

    Route::get(
        '/saran',
        [FeedbackController::class, 'index']
    )->name('saran');

    Route::post(
        '/saran',
        [FeedbackController::class, 'store']
    )->name('saran.store');


    // Logbook.

    Route::get(
        '/logbook',
        [LogbookController::class, 'index']
    )->name('logbook');

    Route::post(
        '/logbook',
        [LogbookController::class, 'store']
    )->name('logbook.store');

    Route::post(
        '/logbook/submit-month',
        [LogbookController::class, 'submitMonth']
    )->name('logbook.submit-month');

    Route::put(
        '/logbook/{id}',
        [LogbookController::class, 'update']
    )->whereNumber('id')->name('logbook.update');

    Route::delete(
        '/logbook/{id}',
        [LogbookController::class, 'destroy']
    )->whereNumber('id')->name('logbook.destroy');


    // Riwayat absensi.

    // Logika riwayat dipusatkan di AttendanceController (dipakai juga oleh ekspor).
    Route::get(
        '/riwayat-absen',
        [AttendanceController::class, 'history']
    )->name('riwayat.absen');

    Route::get(
        '/riwayat-absen/export-pdf',
        [AttendanceController::class, 'exportPdf']
    )->name('riwayat.export-pdf');

    Route::get(
        '/riwayat-absen/export-csv',
        [AttendanceController::class, 'exportCsv']
    )->name('riwayat.export-csv');


    // Pengajuan izin/sakit.

    Route::get(
        '/pengajuan-izin',
        [LeaveRequestController::class, 'create']
    )->name('izin.create');

    Route::post(
        '/pengajuan-izin',
        [LeaveRequestController::class, 'store']
    )->name('izin.store');

    Route::get(
        '/riwayat-izin',
        [LeaveRequestController::class, 'index']
    )->name('izin.history');

    Route::get(
        '/pengajuan-izin/{id}/surat',
        [LeaveRequestController::class, 'download']
    )->whereNumber('id')->name('izin.download');

});


/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
| Mengarahkan pengguna berdasarkan role akun.
*/

Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // Normalisasi role agar konsisten dengan AuthController.
    $role = strtolower(trim((string) $user->role));

    if (in_array($role, ['admin', 'hr'], true)) {
        return redirect()->route('admin.dashboard');
    }

    if ($role === 'mentor') {
        return redirect()->route('mentor.dashboard');
    }

    if ($role === 'magang') {
        return redirect()->route('dashboard.magang');
    }

    // Role tidak dikenal: akhiri session.
    auth()->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login')->withErrors([
        'email' => 'Role akun tidak dikenali. Silakan hubungi administrator.',
    ]);

});


/*
|--------------------------------------------------------------------------
| ROUTE MENTOR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    RoleMiddleware::class . ':mentor',
])->prefix('mentor')->group(function () {

    Route::get(
        '/dashboard',
        [MentorDashboardController::class, 'index']
    )->name('mentor.dashboard');


    // Daftar mahasiswa berdasarkan penugasan mentor.

    Route::get('/interns', function (Request $request) {

        $mentor = $request->user();

        $students = User::where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->with('division')
            ->orderBy('name')
            ->get();

        $assignmentMissing = $students->isEmpty();

        return view('mentor.interns', compact(
            'mentor',
            'students',
            'assignmentMissing'
        ));

    })->name('mentor.interns');

    // Rekap kehadiran per mahasiswa bimbingan.

    Route::get(
        '/interns/{user}/rekap',
        [InternRecapController::class, 'mentorShow']
    )->whereNumber('user')->name('mentor.interns.recap');

    Route::get(
        '/interns/{user}/rekap/pdf',
        [InternRecapController::class, 'mentorPdf']
    )->whereNumber('user')->name('mentor.interns.recap-pdf');


    // Logbook.

    Route::get(
        '/logbook',
        [MentorLogbookController::class, 'index']
    )->name('mentor.logbook');

    Route::post(
        '/logbook/{id}/approve',
        [MentorLogbookController::class, 'approve']
    )->whereNumber('id')->name('mentor.logbook.approve');

    Route::post(
        '/logbook/{id}/reject',
        [MentorLogbookController::class, 'reject']
    )->whereNumber('id')->name('mentor.logbook.reject');


    // Izin/sakit.

    Route::get(
        '/leave-requests',
        [MentorLeaveRequestController::class, 'index']
    )->name('mentor.leave-requests');

    Route::post(
        '/leave-requests/{id}/approve',
        [MentorLeaveRequestController::class, 'approve']
    )->whereNumber('id')->name('mentor.leave-requests.approve');

    Route::post(
        '/leave-requests/{id}/reject',
        [MentorLeaveRequestController::class, 'reject']
    )->whereNumber('id')->name('mentor.leave-requests.reject');

    Route::get(
        '/leave-requests/{id}/document',
        [MentorLeaveRequestController::class, 'download']
    )->whereNumber('id')->name('mentor.leave-requests.document');


    // Halaman absensi mentor: mengambil data dari database.

    Route::get(
        '/attendance',
        [MentorAttendanceController::class, 'index']
    )->name('mentor.attendance');


    // Halaman penilaian mentor.
    // Sementara tetap menggunakan view lama sampai controller
    // dan fitur penyimpanan penilaian selesai dibuat.

    Route::get('/assessment', function () {
        return view('mentor.assessment');
    })->name('mentor.assessment');

});


/*
|--------------------------------------------------------------------------
| ROUTE ADMIN / HR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    RoleMiddleware::class . ':admin,hr',
])->prefix('admin')->group(function () {


    // Dashboard.

    Route::get(
        '/dashboard',
        [AdminController::class, 'dashboard']
    )->name('admin.dashboard');


    // Manajemen pengguna.

    Route::get(
        '/users',
        [AdminController::class, 'users']
    )->name('admin.users');

    Route::put(
        '/users/{user}/mentor',
        [AdminController::class, 'updateUserMentor']
    )->whereNumber('user')->name('admin.users.mentor');

    Route::put(
        '/users/{user}/divisi',
        [AdminController::class, 'updateUserDivision']
    )->whereNumber('user')->name('admin.users.division');

    Route::put(
        '/users/{user}/periode-magang',
        [AdminController::class, 'updateInternshipPeriod']
    )->whereNumber('user')->name('admin.users.internship-period');

    Route::put(
        '/users/{user}/location',
        [AdminController::class, 'updateUserLocation']
    )->whereNumber('user')->name('admin.users.location');


    // CRUD Divisi.

    Route::get(
        '/divisions',
        [AdminController::class, 'divisions']
    )->name('admin.divisions');

    Route::post(
        '/divisions',
        [AdminController::class, 'storeDivision']
    )->name('admin.divisions.store');

    Route::put(
        '/divisions/{division}',
        [AdminController::class, 'updateDivision']
    )->whereNumber('division')->name('admin.divisions.update');

    Route::delete(
        '/divisions/{division}',
        [AdminController::class, 'destroyDivision']
    )->whereNumber('division')->name('admin.divisions.destroy');


    // Rekap absensi.

    Route::get(
        '/attendance',
        [AdminController::class, 'attendance']
    )->name('admin.attendance');

    // Ekspor memakai seluruh data sesuai filter aktif (bukan hanya halaman yang tampil).
    Route::get(
        '/attendance/export-csv',
        [AdminController::class, 'exportAttendanceCsv']
    )->name('admin.attendance.export-csv');

    Route::get(
        '/attendance/export-pdf',
        [AdminController::class, 'exportAttendancePdf']
    )->name('admin.attendance.export-pdf');

    Route::get(
        '/attendance/{attendance}/photo',
        [AdminController::class, 'attendancePhoto']
    )->whereNumber('attendance')->name('admin.attendance.photo');


    // Pengaturan sistem.

    Route::get(
        '/settings',
        [SystemSettingsController::class, 'edit']
    )->name('admin.settings');

    Route::put(
        '/settings',
        [SystemSettingsController::class, 'update']
    )->name('admin.settings.update');


    // Rekap kehadiran per peserta.

    Route::get(
        '/interns/{user}/rekap',
        [InternRecapController::class, 'adminShow']
    )->whereNumber('user')->name('admin.interns.recap');

    Route::get(
        '/interns/{user}/rekap/pdf',
        [InternRecapController::class, 'adminPdf']
    )->whereNumber('user')->name('admin.interns.recap-pdf');


    // Saran & masukan peserta.

    Route::get(
        '/feedbacks',
        [AdminFeedbackController::class, 'index']
    )->name('admin.feedbacks');

    Route::put(
        '/feedbacks/{feedback}',
        [AdminFeedbackController::class, 'update']
    )->whereNumber('feedback')->name('admin.feedbacks.update');


    // Manajemen Kalender Hari Libur.

    Route::get(
        '/holidays',
        [HolidayController::class, 'index']
    )->name('admin.holidays');

    Route::post(
        '/holidays',
        [HolidayController::class, 'store']
    )->name('admin.holidays.store');

    Route::post(
        '/holidays/import',
        [HolidayController::class, 'import']
    )->name('admin.holidays.import');

    Route::get(
        '/holidays/template',
        [HolidayController::class, 'template']
    )->name('admin.holidays.template');

    Route::put(
        '/holidays/{id}',
        [HolidayController::class, 'update']
    )->whereNumber('id')->name('admin.holidays.update');

    Route::delete(
        '/holidays/{id}',
        [HolidayController::class, 'destroy']
    )->whereNumber('id')->name('admin.holidays.destroy');


    // Manajemen lokasi kantor untuk absensi geospasial.

    Route::get(
        '/locations',
        [AdminLocationController::class, 'index']
    )->name('admin.locations');

    Route::post(
        '/locations',
        [AdminLocationController::class, 'store']
    )->name('admin.locations.store');

    Route::put(
        '/locations/{location}',
        [AdminLocationController::class, 'update']
    )->whereNumber('location')->name('admin.locations.update');

    Route::delete(
        '/locations/{location}',
        [AdminLocationController::class, 'destroy']
    )->whereNumber('location')->name('admin.locations.destroy');

});
