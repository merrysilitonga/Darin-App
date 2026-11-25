<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mahasiswa yang daftar sendiri harus disetujui Admin/HR sebelum bisa login.
 */
class PersetujuanPendaftarTest extends TestCase
{
    use RefreshDatabase;

    private function daftar(): User
    {
        $this->post(route('register'), [
            'role' => 'magang',
            'name' => 'Calon Peserta',
            'email' => 'calon@kampus.ac.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'university' => 'Universitas Uji',
            'domicile' => 'Bekasi',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();

        return User::where('email', 'calon@kampus.ac.id')->firstOrFail();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function lokasi(bool $aktif = true): Location
    {
        return Location::create([
            'name' => 'Kementerian ESDM',
            'latitude' => -6.226111,
            'longitude' => 106.833359,
            'radius_meters' => 200,
            'is_active' => $aktif,
        ]);
    }

    public function test_pendaftar_baru_tidak_bisa_login_sebelum_disetujui(): void
    {
        $this->daftar();

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'calon@kampus.ac.id', 'password' => 'rahasia123'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_melihat_pendaftar_di_manajemen_pengguna_dan_dashboard(): void
    {
        $this->daftar();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Pendaftar Baru')
            ->assertSee('calon@kampus.ac.id')
            ->assertSee('Setujui Pendaftaran');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 pendaftar baru');
    }

    public function test_admin_menyetujui_lalu_mahasiswa_bisa_login(): void
    {
        $calon = $this->daftar();
        $lokasi = $this->lokasi();
        $mentor = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($this->admin())
            ->from(route('admin.users'))
            ->post(route('admin.users.approve', $calon->id), [
                'location_id' => $lokasi->id,
                'mentor_id' => $mentor->id,
                'start_date' => '2026-11-01',
                'end_date' => '2026-12-31',
            ])
            ->assertSessionHas('success');

        $calon->refresh();
        $this->assertSame('Disetujui', $calon->approval_status);
        $this->assertSame($lokasi->id, (int) $calon->location_id);
        $this->assertSame($mentor->id, (int) $calon->mentor_id);
        $this->assertSame('2026-11-01', substr((string) $calon->getRawOriginal('start_date'), 0, 10));

        auth()->logout();

        $this->post(route('login'), ['email' => 'calon@kampus.ac.id', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard.magang'));
    }

    public function test_persetujuan_wajib_lokasi_aktif_dan_periode(): void
    {
        $calon = $this->daftar();
        $nonaktif = $this->lokasi(false);

        $this->actingAs($this->admin())
            ->from(route('admin.users'))
            ->post(route('admin.users.approve', $calon->id), [
                'location_id' => $nonaktif->id,
                'start_date' => '2026-12-31',
                'end_date' => '2026-11-01',
            ])
            ->assertSessionHasErrors(['location_id', 'end_date']);

        $this->assertSame('Menunggu', $calon->fresh()->approval_status);
    }

    public function test_admin_menolak_pendaftar_akun_dihapus(): void
    {
        $calon = $this->daftar();

        $this->actingAs($this->admin())
            ->from(route('admin.users'))
            ->delete(route('admin.users.reject', $calon->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $calon->id]);
    }

    public function test_tolak_tidak_berlaku_untuk_akun_yang_sudah_disetujui(): void
    {
        $magang = User::factory()->create(['role' => 'magang']);

        $this->actingAs($this->admin())
            ->delete(route('admin.users.reject', $magang->id))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $magang->id]);
    }

    public function test_mentor_tidak_bisa_menyetujui(): void
    {
        $calon = $this->daftar();
        $mentor = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($mentor)
            ->post(route('admin.users.approve', $calon->id), [])
            ->assertForbidden();
    }

    public function test_domisili_wajib_dan_tersimpan(): void
    {
        $this->from(route('register'))
            ->post(route('register'), [
                'role' => 'magang',
                'name' => 'Tanpa Domisili',
                'email' => 'tanpa@kampus.ac.id',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors('domicile');

        $calon = $this->daftar();
        $this->assertSame('Bekasi', $calon->domicile);

        $this->actingAs($this->admin())
            ->get(route('admin.users'))
            ->assertSee('Domisili Bekasi');
    }

    public function test_halaman_daftar_tanpa_pilihan_jenis_akun(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="domicile"', false)
            ->assertSee('name="role"', false)
            ->assertDontSee('class="role-option"', false)
            ->assertDontSee('Akun Mentor dan Admin/HR tidak dapat didaftarkan di sini');
    }
}
