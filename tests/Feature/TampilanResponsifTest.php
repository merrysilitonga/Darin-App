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
            ->assertSee('data-sidebar-buka', false)
            ->assertSee('id="darin-sidebar"', false);
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

    public function test_dashboard_admin_memakai_layout_dan_menu_yang_sama(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'name' => 'Admin Uji']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-buka', false)
            ->assertSee('Lokasi Absensi')
            ->assertSee('Selamat Datang, Admin Uji!')
            ->assertSee('Tren Kehadiran 30 Hari Terakhir');
    }

    public function test_kartu_pintasan_dashboard_sesuai_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('href="' . route('admin.logbooks') . '"', false)
            ->assertSee('Danger Zone (Reset)');

        $this->actingAs(User::factory()->create(['role' => 'hr']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Danger Zone (Reset)');
    }

    public function test_halaman_login_memakai_font_yang_sama(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Plus+Jakarta+Sans', false);
    }

    public function test_halaman_admin_memakai_sidebar_bersama_dengan_kartu_profil(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Rizal Admin']);

        // Halaman admin-layout & halaman lokasi kini memakai sidebar yang sama.
        foreach (['admin.attendance', 'admin.locations', 'admin.dashboard'] as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk()
                ->assertSee('id="darin-sidebar"', false)
                ->assertSee('PROFIL')
                ->assertSee('Rizal Admin')
                ->assertDontSee('Portal HR / Admin');
        }
    }

    public function test_halaman_penilaian_mentor_memakai_layout_mentor(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Uji']))
            ->get(route('mentor.assessment'))
            ->assertOk()
            ->assertSee('id="darin-sidebar"', false)
            ->assertSee('Penilaian Akhir Magang')
            ->assertSee('Mentor Uji')
            ->assertSee('Tidak ada tindakan')
            ->assertDontSee('Portal Mentor</p>', false);
    }
}
