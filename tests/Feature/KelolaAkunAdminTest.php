<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Admin/HR mengubah data akun dan menonaktifkan/mengaktifkan akun.
 */
class KelolaAkunAdminTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $admin;
    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->magang = $this->buatMagang(null, [
            'name' => 'Nama Lama',
            'email' => 'lama@kampus.ac.id',
            'password' => Hash::make('rahasia123'),
        ]);
    }

    public function test_admin_mengubah_data_akun_peserta(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.users'))
            ->put(route('admin.users.account', $this->magang), [
                'name' => 'Nama Baru',
                'email' => 'BARU@kampus.ac.id',
                'nim' => '2101234567',
                'university' => 'Universitas Bina Sarana Informatika',
                'study_program' => 'Sistem Informasi',
                'phone' => '081234567890',
            ])
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('success');

        $magang = $this->magang->fresh();
        $this->assertSame('Nama Baru', $magang->name);
        $this->assertSame('baru@kampus.ac.id', $magang->email);
        $this->assertSame('2101234567', $magang->nim);
        $this->assertSame('Universitas Bina Sarana Informatika', $magang->university);
        // Role tidak ikut berubah.
        $this->assertSame('magang', $magang->role);
    }

    public function test_email_tidak_boleh_sama_dengan_akun_lain(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.users.account', $this->magang), [
                'name' => 'Nama Lama',
                'email' => $this->admin->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame('lama@kampus.ac.id', $this->magang->fresh()->email);
    }

    public function test_akun_nonaktif_tidak_bisa_login_dan_bisa_diaktifkan_kembali(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.status', $this->magang))
            ->assertSessionHas('success');

        $this->assertFalse($this->magang->fresh()->is_active);

        // Halaman admin menandai akun nonaktif.
        $this->actingAs($this->admin)
            ->get(route('admin.users'))
            ->assertSee('NONAKTIF')
            ->assertSee('Aktifkan Kembali');

        auth()->logout();

        $this->post(route('login'), ['email' => 'lama@kampus.ac.id', 'password' => 'rahasia123'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // Diaktifkan kembali → bisa login.
        $this->actingAs($this->admin)->patch(route('admin.users.status', $this->magang));
        $this->assertTrue($this->magang->fresh()->is_active);
        auth()->logout();

        $this->post(route('login'), ['email' => 'lama@kampus.ac.id', 'password' => 'rahasia123']);
        $this->assertAuthenticatedAs($this->magang->fresh());
    }

    public function test_akun_yang_dinonaktifkan_saat_login_langsung_dikeluarkan(): void
    {
        $this->magang->forceFill(['is_active' => false])->save();

        $this->actingAs($this->magang->fresh())
            ->get(route('absen'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_tidak_bisa_mengubah_akun_sendiri_dan_hr_tidak_bisa_mengubah_admin(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.status', $this->admin))
            ->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->isActive());

        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hr)
            ->patch(route('admin.users.status', $this->admin))
            ->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->isActive());

        $this->actingAs($hr)
            ->put(route('admin.users.account', $this->admin), ['name' => 'Diubah HR', 'email' => 'x@y.id'])
            ->assertSessionHas('error');
        $this->assertNotSame('Diubah HR', $this->admin->fresh()->name);

        // HR tetap boleh mengelola akun peserta.
        $this->actingAs($hr)
            ->patch(route('admin.users.status', $this->magang))
            ->assertSessionHas('success');
        $this->assertFalse($this->magang->fresh()->is_active);
    }

    public function test_daftar_pengguna_hanya_mentor_dan_mahasiswa(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'email' => 'mentor@esdm.go.id']);
        $hr = User::factory()->create(['role' => 'hr', 'email' => 'hr@esdm.go.id']);

        $this->actingAs($this->admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('lama@kampus.ac.id')
            ->assertSee('mentor@esdm.go.id')
            ->assertDontSee('hr@esdm.go.id')
            ->assertDontSee($this->admin->email);
    }
}
