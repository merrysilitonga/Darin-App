<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Keamanan akun: pembatasan percobaan login.
 */
class KeamananAkunTest extends TestCase
{
    use RefreshDatabase;

    private function dataDaftar(array $override = []): array
    {
        return array_merge([
            'role' => 'magang',
            'name' => 'Peserta Baru',
            'email' => 'peserta@kampus.ac.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'domicile' => 'Jakarta',
        ], $override);
    }

    public function test_pendaftaran_tetap_berjalan_normal(): void
    {
        $this->post(route('register'), $this->dataDaftar())
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'peserta@kampus.ac.id',
            'role' => 'magang',
            'approval_status' => 'Menunggu',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PEMBATASAN LOGIN
    |--------------------------------------------------------------------------
    */

    public function test_login_dikunci_sementara_setelah_lima_kali_gagal(): void
    {
        $user = User::factory()->create([
            'role' => 'magang',
            'email' => 'magang@darin.test',
            'password' => bcrypt('rahasia123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('login'))->post(route('login'), [
                'email' => $user->email,
                'password' => 'salah',
            ]);
        }

        // Password benar pun ditolak selama masih dikunci.
        $this->from(route('login'))
            ->post(route('login'), ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString(
            'Terlalu banyak percobaan login',
            session('errors')->first('email')
        );
    }

    public function test_login_berhasil_mereset_hitungan_gagal(): void
    {
        $user = User::factory()->create([
            'role' => 'magang',
            'email' => 'magang@darin.test',
            'password' => bcrypt('rahasia123'),
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->from(route('login'))->post(route('login'), [
                'email' => $user->email,
                'password' => 'salah',
            ]);
        }

        $this->post(route('login'), ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.magang'));

        $this->assertAuthenticatedAs($user);
    }
}
