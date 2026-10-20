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
        // 1. Buat Divisi Contoh
        $divTeknologi = Division::create([
            'name' => 'Teknologi Informasi',
            'description' => 'Divisi pengembang perangkat lunak dan infrastruktur IT.'
        ]);

        $divKehumasan = Division::create([
            'name' => 'Kehumasan & Layanan',
            'description' => 'Divisi pelayanan masyarakat dan komunikasi.'
        ]);

        // 2. Buat Akun Admin
        User::create([
            'name' => 'Administrator HR',
            'email' => 'admin@darin.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'division_id' => null,
        ]);

        // 3. Buat Akun Mentor
        User::create([
            'name' => 'Budi Mentor, S.Kom.',
            'email' => 'mentor@darin.test',
            'password' => Hash::make('password123'),
            'role' => 'mentor',
            'division_id' => $divTeknologi->id,
        ]);

        // 4. Buat Akun Peserta Magang (Intern)
        User::create([
            'name' => 'Merry Silitonga',
            'email' => 'merry@darin.test',
            'password' => Hash::make('password123'),
            'role' => 'intern',
            'division_id' => $divTeknologi->id,
        ]);
    }
}