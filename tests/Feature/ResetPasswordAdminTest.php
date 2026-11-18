<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Reset password pengguna oleh Admin/HR (tanpa perlu email).
 */
class ResetPasswordAdminTest extends TestCase
{
    use RefreshDatabase;

    private function buat(string $role, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'password' => bcrypt('passwordlama'),
        ], $atribut));
    }

    public function test_admin_dapat_mereset_password_mahasiswa(): void
    {
        $admin = $this->buat('admin');
        $magang = $this->buat('magang', ['name' => 'Sinta']);

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->put(route('admin.users.password-reset', $magang))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('success');

        $baru = session('password_reset')['password'];

        $this->assertSame(10, strlen($baru));
        $this->assertTrue(Hash::check($baru, $magang->fresh()->password));
        $this->assertFalse(Hash::check('passwordlama', $magang->fresh()->password));

        // Password baru tampil sekali di halaman Manajemen Pengguna.
        $this->actingAs($admin)
            ->withSession(['password_reset' => ['name' => 'Sinta', 'email' => $magang->email, 'password' => $baru]])
            ->get(route('admin.users'))
            ->assertSee($baru)
            ->assertSee('hanya ditampilkan sekali', false);
    }

    public function test_pengguna_bisa_login_dengan_password_baru(): void
    {
        $admin = $this->buat('admin');
        $magang = $this->buat('magang');

        $this->actingAs($admin)->put(route('admin.users.password-reset', $magang));
        $baru = session('password_reset')['password'];

        $this->post(route('logout'));

        $this->post(route('login'), ['email' => $magang->email, 'password' => $baru])
            ->assertRedirect(route('dashboard.magang'));

        $this->assertAuthenticatedAs($magang);
    }

    public function test_admin_tidak_bisa_mereset_password_sendiri_di_sini(): void
    {
        $admin = $this->buat('admin');

        $this->actingAs($admin)
            ->put(route('admin.users.password-reset', $admin))
            ->assertSessionHas('error');

        $this->assertTrue(Hash::check('passwordlama', $admin->fresh()->password));
    }

    public function test_hr_tidak_bisa_mereset_password_admin(): void
    {
        $hr = $this->buat('hr');
        $admin = $this->buat('admin');

        $this->actingAs($hr)
            ->put(route('admin.users.password-reset', $admin))
            ->assertSessionHas('error');

        $this->assertTrue(Hash::check('passwordlama', $admin->fresh()->password));
    }

    public function test_hr_dapat_mereset_password_mentor(): void
    {
        $hr = $this->buat('hr');
        $mentor = $this->buat('mentor');

        $this->actingAs($hr)
            ->put(route('admin.users.password-reset', $mentor))
            ->assertSessionHas('success');

        $this->assertFalse(Hash::check('passwordlama', $mentor->fresh()->password));
    }

    public function test_magang_tidak_bisa_mereset_password_orang_lain(): void
    {
        $magang = $this->buat('magang');
        $lain = $this->buat('magang');

        $this->actingAs($magang)
            ->put(route('admin.users.password-reset', $lain))
            ->assertForbidden();

        $this->assertTrue(Hash::check('passwordlama', $lain->fresh()->password));
    }
}
