<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pendaftaran publik hanya untuk mahasiswa; Admin dibuat lewat artisan.
 */
class PendaftaranPublikTest extends TestCase
{
    use RefreshDatabase;

    private function dataDaftar(string $role): array
    {
        return [
            'role' => $role,
            'name' => 'Penyusup',
            'email' => 'penyusup@luar.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ];
    }

    public function test_halaman_daftar_tidak_menawarkan_admin_dan_mentor(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('data-role="admin"', false)
            ->assertDontSee('data-role="mentor"', false);
    }

    public function test_tidak_bisa_daftar_sebagai_admin(): void
    {
        $this->from(route('register'))
            ->post(route('register'), $this->dataDaftar('admin'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@luar.test']);
        $this->assertGuest();
    }

    public function test_tidak_bisa_daftar_sebagai_mentor(): void
    {
        $this->from(route('register'))
            ->post(route('register'), $this->dataDaftar('mentor'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@luar.test']);
    }

    public function test_perintah_buat_admin(): void
    {
        $this->artisan('darin:buat-admin')
            ->expectsQuestion('Nama lengkap', 'Admin ESDM')
            ->expectsQuestion('Email', 'Admin@ESDM.go.id')
            ->expectsQuestion('Password (minimal 8 karakter)', 'rahasia123')
            ->expectsQuestion('Ulangi password', 'rahasia123')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin@esdm.go.id')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia123', $admin->password));
    }

    public function test_perintah_buat_admin_menolak_email_ganda(): void
    {
        User::factory()->create(['email' => 'admin@esdm.go.id']);

        $this->artisan('darin:buat-admin')
            ->expectsQuestion('Nama lengkap', 'Admin Kedua')
            ->expectsQuestion('Email', 'admin@esdm.go.id')
            ->expectsQuestion('Password (minimal 8 karakter)', 'rahasia123')
            ->expectsQuestion('Ulangi password', 'rahasia123')
            ->expectsOutput('Email tersebut sudah terdaftar.')
            ->assertExitCode(1);
    }

    public function test_admin_punya_tombol_dan_bisa_mendaftarkan_mentor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee(route('mentor.register'), false);

        $this->actingAs($admin)
            ->get(route('mentor.register'))
            ->assertOk();
    }

    public function test_mahasiswa_tidak_bisa_membuka_pendaftaran_mentor(): void
    {
        $magang = User::factory()->create(['role' => 'magang']);

        $this->actingAs($magang)
            ->get(route('mentor.register'))
            ->assertForbidden();
    }
}
