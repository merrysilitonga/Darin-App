<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| BUAT AKUN ADMIN
|--------------------------------------------------------------------------
| Pendaftaran publik hanya untuk mahasiswa, jadi Admin pertama dibuat di sini:
|   php artisan darin:buat-admin
*/
Artisan::command('darin:buat-admin {--role=admin : admin atau hr}', function () {
    $role = $this->option('role');

    if (!in_array($role, ['admin', 'hr'], true)) {
        $this->error('Role harus admin atau hr.');

        return 1;
    }

    $nama = trim((string) $this->ask('Nama lengkap'));
    $email = strtolower(trim((string) $this->ask('Email')));
    $password = (string) $this->secret('Password (minimal 8 karakter)');
    $konfirmasi = (string) $this->secret('Ulangi password');

    $validator = \Illuminate\Support\Facades\Validator::make(
        ['name' => $nama, 'email' => $email, 'password' => $password, 'password_confirmation' => $konfirmasi],
        [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],
        [
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]
    );

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $pesan) {
            $this->error($pesan);
        }

        return 1;
    }

    $user = new \App\Models\User();
    $user->name = $nama;
    $user->email = $email;
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->role = $role;
    $user->save();

    $this->info("Akun {$role} {$email} berhasil dibuat. Silakan login di /login.");

    return 0;
})->purpose('Membuat akun Admin/HR DARIN');
