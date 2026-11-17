<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Tahap 1 UI/UX: sidebar bisa dibuka lewat tombol ☰ di layar HP.
 */
class TampilanResponsifTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');
    }

    public function test_halaman_mahasiswa_punya_tombol_menu_hp(): void
    {
        $magang = $this->buatMagang();

        foreach (['dashboard.magang', 'absen', 'riwayat.absen', 'logbook', 'saran'] as $route) {
            $this->actingAs($magang)
                ->get(route($route))
                ->assertOk()
                ->assertSee('data-sidebar-buka', false)
                ->assertSee('id="darin-sidebar"', false);
        }
    }

    public function test_halaman_mentor_punya_tombol_menu_hp(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'mentor']))
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-buka', false);
    }

    public function test_halaman_admin_layout_punya_tombol_menu_hp(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.attendance'))
            ->assertOk()
            ->assertSee('data-admin-sidebar-buka', false)
            ->assertSee('id="darin-admin-sidebar"', false);
    }

    public function test_menu_admin_sama_di_semua_kerangka_halaman(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // admin.users memakai layouts.sidebar, admin.attendance memakai layouts.admin-layout.
        foreach (['admin.users', 'admin.attendance'] as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk()
                ->assertSeeInOrder([
                    'Dashboard Utama',
                    'Manajemen Pengguna',
                    'Penempatan &amp; Divisi',
                    'Lokasi Absensi',
                    'Rekap Absensi &amp; Logbook',
                    'Monitoring Logbook',
                    'Saran &amp; Masukan',
                    'Kalender Hari Libur',
                    'Pengaturan Sistem',
                    'Reset Data',
                ], false);
        }
    }

    public function test_menu_reset_data_tidak_tampil_untuk_hr(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr']))
            ->get(route('admin.attendance'))
            ->assertOk()
            ->assertSee('Monitoring Logbook')
            ->assertDontSee('Reset Data');
    }

    public function test_admin_layout_memakai_tailwind_cdn(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.attendance'))
            ->assertOk()
            ->assertSee('cdn.tailwindcss.com', false);
    }
}
