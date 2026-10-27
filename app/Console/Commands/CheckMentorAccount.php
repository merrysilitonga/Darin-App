<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckMentorAccount extends Command
{
    /**
     * Format perintah Artisan:
     * php artisan mentor:check email@example.com
     */
    protected $signature = 'mentor:check {email}';

    /**
     * Deskripsi perintah.
     */
    protected $description = 'Memeriksa role akun mentor berdasarkan email';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)
            ->first(['id', 'name', 'email', 'role']);

        if (!$user) {
            $this->error('Akun tidak ditemukan di database Laravel.');

            return self::FAILURE;
        }

        $this->info('ID    : ' . $user->id);
        $this->info('Nama  : ' . $user->name);
        $this->info('Email : ' . $user->email);
        $this->info('Role  : ' . $user->role);

        return self::SUCCESS;
    }
}