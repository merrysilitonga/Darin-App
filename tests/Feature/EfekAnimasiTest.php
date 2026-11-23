<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Efek animasi (Motion + GSAP) dipasang lewat layouts/sidebar, jadi harus ada
 * di halaman semua role yang sudah login.
 */
class EfekAnimasiTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');
    }

    private function assertAdaEfek($respons): void
    {
        $respons->assertOk()
            ->assertSee('gsap@3.15.0/dist/gsap.min.js', false)
            ->assertSee('gsap@3.15.0/dist/ScrollTrigger.min.js', false)
            ->assertSee('gsap@3.15.0/dist/SplitText.min.js', false)
            ->assertSee('SplitText.create', false)
            // Motion untuk kartu masuk & hover.
            ->assertSee('https://cdn.jsdelivr.net/npm/motion@14.11.1/+esm', false)
            ->assertSee('hover(kartuKecil', false)
            ->assertSee('inView(kartu', false)
            // Lenis untuk smooth scroll, disinkronkan dengan GSAP ScrollTrigger.
            ->assertSee('https://unpkg.com/lenis@1.3.26/dist/lenis.min.js', false)
            ->assertSee('https://unpkg.com/lenis@1.3.26/dist/lenis.css', false)
            ->assertSee("lenis.on('scroll', ScrollTrigger.update)", false)
            // Animasi dimatikan jika perangkat meminta "kurangi animasi".
            ->assertSee('prefers-reduced-motion: reduce', false);

        // Dimuat sekali saja per halaman.
        $this->assertSame(1, substr_count($respons->getContent(), 'gsap@3.15.0/dist/gsap.min.js'));
    }

    public function test_efek_animasi_ada_di_halaman_mahasiswa(): void
    {
        $magang = $this->buatMagang($this->buatLokasi());

        $this->assertAdaEfek($this->actingAs($magang)->get(route('absen')));
        $this->assertAdaEfek($this->actingAs($magang)->get(route('riwayat.absen')));
    }

    public function test_efek_animasi_ada_di_halaman_mentor_dan_admin(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertAdaEfek($this->actingAs($mentor)->get(route('mentor.attendance')));
        $this->assertAdaEfek($this->actingAs($admin)->get(route('admin.attendance')));
    }

    public function test_halaman_login_tidak_memuat_gsap(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('gsap@3.15.0', false)
            ->assertDontSee('motion@14.11.1', false)
            ->assertDontSee('lenis@1.3.26', false);
    }
}
