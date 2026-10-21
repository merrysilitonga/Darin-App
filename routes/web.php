<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Attendance;

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
| REGISTER
|--------------------------------------------------------------------------
*/

Route::get('/register', [AuthController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [AuthController::class, 'processRegister'])
    ->middleware('guest');

Route::get('/register/mentor', [AuthController::class, 'showMentorRegister'])
    ->middleware('guest')
    ->name('mentor.register');

Route::post('/register/mentor', [AuthController::class, 'processMentorRegister'])
    ->middleware('guest')
    ->name('mentor.register.process');


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
                'password' => $password,
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
*/

Route::middleware([
    'auth',
    RoleMiddleware::class . ':magang,intern',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD MAHASISWA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard-magang',
        [DashboardController::class, 'index']
    )->name('dashboard.magang');


    /*
    |--------------------------------------------------------------------------
    | ABSENSI MAHASISWA
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | LOGBOOK MAHASISWA
    |--------------------------------------------------------------------------
    */

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
    )->whereNumber('id')
        ->name('logbook.update');

    Route::delete(
        '/logbook/{id}',
        [LogbookController::class, 'destroy']
    )->whereNumber('id')
        ->name('logbook.destroy');


    /*
    |--------------------------------------------------------------------------
    | RIWAYAT ABSENSI MAHASISWA
    |--------------------------------------------------------------------------
    */

    Route::get('/riwayat-absen', function (Request $request) {

        $user = $request->user();
        $bulan = $request->query('bulan');

        if (
            !is_string($bulan) ||
            !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)
        ) {
            $bulan = Carbon::now('Asia/Jakarta')->format('Y-m');
        }

        $periode = Carbon::createFromFormat(
            '!Y-m',
            $bulan,
            'Asia/Jakarta'
        );

        $tanggalAwal = $periode->copy()
            ->startOfMonth()
            ->toDateString();

        $tanggalAkhir = $periode->copy()
            ->endOfMonth()
            ->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $tanggalAwal,
                $tanggalAkhir,
            ])
            ->orderByDesc('date')
            ->orderByDesc('clock_in')
            ->get();

        return view(
            'attendance.history',
            compact('user', 'attendances', 'periode')
        );

    })->name('riwayat.absen');


    /*
    |--------------------------------------------------------------------------
    | EKSPOR RIWAYAT ABSENSI MAHASISWA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/riwayat-absen/export-pdf',
        [AttendanceController::class, 'exportPdf']
    )->name('riwayat.export-pdf');

    Route::get(
        '/riwayat-absen/export-csv',
        [AttendanceController::class, 'exportCsv']
    )->name('riwayat.export-csv');


    /*
    |--------------------------------------------------------------------------
    | PENGAJUAN IZIN / SAKIT MAHASISWA
    |--------------------------------------------------------------------------
    */

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
    )->whereNumber('id')
        ->name('izin.download');

});


/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if (in_array($user->role, ['admin', 'hr'], true)) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'mentor') {
        return redirect()->route('mentor.dashboard');
    }

    if (in_array($user->role, ['magang', 'intern'], true)) {
        return redirect()->route('dashboard.magang');
    }

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
])
    ->prefix('mentor')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD MENTOR
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [MentorDashboardController::class, 'index']
        )->name('mentor.dashboard');


        /*
        |--------------------------------------------------------------------------
        | DAFTAR MAHASISWA BIMBINGAN
        |--------------------------------------------------------------------------
        */

        Route::get('/interns', function () {
            return view('mentor.interns');
        })->name('mentor.interns');


        /*
        |--------------------------------------------------------------------------
        | REVIEW LOGBOOK MENTOR
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/logbook',
            [MentorLogbookController::class, 'index']
        )->name('mentor.logbook');

        Route::post(
            '/logbook/{id}/approve',
            [MentorLogbookController::class, 'approve']
        )->whereNumber('id')
            ->name('mentor.logbook.approve');

        Route::post(
            '/logbook/{id}/reject',
            [MentorLogbookController::class, 'reject']
        )->whereNumber('id')
            ->name('mentor.logbook.reject');


        /*
        |--------------------------------------------------------------------------
        | PERSETUJUAN IZIN / SAKIT MENTOR
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/leave-requests',
            [MentorLeaveRequestController::class, 'index']
        )->name('mentor.leave-requests');

        Route::post(
            '/leave-requests/{id}/approve',
            [MentorLeaveRequestController::class, 'approve']
        )->whereNumber('id')
            ->name('mentor.leave-requests.approve');

        Route::post(
            '/leave-requests/{id}/reject',
            [MentorLeaveRequestController::class, 'reject']
        )->whereNumber('id')
            ->name('mentor.leave-requests.reject');

        Route::get(
            '/leave-requests/{id}/document',
            [MentorLeaveRequestController::class, 'download']
        )->whereNumber('id')
            ->name('mentor.leave-requests.document');


        /*
        |--------------------------------------------------------------------------
        | REKAP ABSENSI MENTOR
        |--------------------------------------------------------------------------
        */

        Route::get('/attendance', function () {
            return view('mentor.attendance');
        })->name('mentor.attendance');


        /*
        |--------------------------------------------------------------------------
        | PENILAIAN MENTOR
        |--------------------------------------------------------------------------
        */

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
])
    ->prefix('admin')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD ADMIN
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [AdminController::class, 'dashboard']
        )->name('admin.dashboard');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN PENGGUNA
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users',
            [AdminController::class, 'users']
        )->name('admin.users');


        /*
        |--------------------------------------------------------------------------
        | PENGATURAN PERIODE MAGANG
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/users/{user}/periode-magang',
            [AdminController::class, 'updateInternshipPeriod']
        )->whereNumber('user')
            ->name('admin.users.internship-period');


        /*
        |--------------------------------------------------------------------------
        | PENETAPAN DIVISI MAHASISWA / MENTOR
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/users/{user}/divisi',
            [AdminController::class, 'updateUserDivision']
        )->whereNumber('user')
            ->name('admin.users.division');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN DIVISI
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/divisions',
            [AdminController::class, 'divisions']
        )->name('admin.divisions');


        /*
        |--------------------------------------------------------------------------
        | REKAP ABSENSI ADMIN
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/attendance',
            [AdminController::class, 'attendance']
        )->name('admin.attendance');

        Route::get(
            '/attendance/{attendance}/photo',
            [AdminController::class, 'attendancePhoto']
        )->whereNumber('attendance')
            ->name('admin.attendance.photo');


        /*
        |--------------------------------------------------------------------------
        | PENGATURAN SISTEM
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/settings',
            [SystemSettingsController::class, 'edit']
        )->name('admin.settings');

        Route::put(
            '/settings',
            [SystemSettingsController::class, 'update']
        )->name('admin.settings.update');

    });