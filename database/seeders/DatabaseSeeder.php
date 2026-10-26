<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat divisi jika belum tersedia.
        $divTeknologi = Division::firstOrCreate(
            ['name' => 'Teknologi Informasi'],
            [
                'description' => 'Divisi pengembang perangkat lunak dan infrastruktur IT.',
            ]
        );

        Division::firstOrCreate(
            ['name' => 'Kehumasan & Layanan'],
            [
                'description' => 'Divisi pelayanan masyarakat dan komunikasi.',
            ]
        );

        // Akun Admin khusus pengembangan/pengujian.
        User::updateOrCreate(
            ['email' => 'admin@darin.test'],
            [
                'name' => 'Administrator HR',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'division_id' => null,
            ]
        );

        // Akun Mentor khusus pengembangan/pengujian.
        User::updateOrCreate(
            ['email' => 'mentor@darin.test'],
            [
                'name' => 'Budi Mentor, S.Kom.',
                'password' => Hash::make('password123'),
                'role' => 'mentor',
                'division_id' => $divTeknologi->id,
            ]
        );

        // Akun Mahasiswa Magang.
        // Role mahasiswa menggunakan standar "magang".
        User::updateOrCreate(
            ['email' => 'merry@darin.test'],
            [
                'name' => 'Merry Silitonga',
                'password' => Hash::make('password123'),
                'role' => 'magang',
                'division_id' => $divTeknologi->id,
            ]
        );
    }
}