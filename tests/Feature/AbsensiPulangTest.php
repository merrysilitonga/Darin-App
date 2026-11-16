<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Aturan bisnis absen pulang (POST /absen-pulang). Jam pulang uji: 16:00.
 */
class AbsensiPulangTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();

        // Setiap tes diawali dengan absen masuk yang sah pada Senin pagi.
        $this->magang = $this->buatMagang($this->buatLokasi());
        $this->setWaktuWib('2026-11-16 07:45:00');

        $this->actingAs($this->magang)
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('success');
    }

    public function test_absen_pulang_sebelum_jam_pulang_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 15:00:00');

        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.checkout'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertNull(Attendance::firstOrFail()->clock_out);
    }

    public function test_absen_pulang_setelah_jam_pulang_dalam_radius_berhasil(): void
    {
        $this->setWaktuWib('2026-11-16 16:30:00');

        $this->actingAs($this->magang)
            ->post(route('absen.checkout'), $this->dataAbsen())
            ->assertRedirect(route('absen'))
            ->assertSessionHas('success');

        $absen = Attendance::firstOrFail();

        $this->assertNotNull($absen->clock_out);
        $this->assertNotNull($absen->clock_out_photo_path);
        Storage::disk('local')->assertExists($absen->clock_out_photo_path);
    }

    public function test_absen_pulang_di_luar_radius_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 16:30:00');

        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.checkout'), $this->dataAbsen($this->koordinatLuarRadius()))
            ->assertSessionHas('error');

        $this->assertNull(Attendance::firstOrFail()->clock_out);
    }

    public function test_absen_pulang_dua_kali_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 16:30:00');

        $this->actingAs($this->magang)->post(route('absen.checkout'), $this->dataAbsen());
        $jamPulangPertama = Attendance::firstOrFail()->clock_out;

        $this->setWaktuWib('2026-11-16 17:00:00');

        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.checkout'), $this->dataAbsen())
            ->assertSessionHas('error');

        // Jam pulang pertama tidak tertimpa.
        $this->assertSame($jamPulangPertama, Attendance::firstOrFail()->clock_out);
    }

    public function test_absen_pulang_tanpa_absen_masuk_ditolak(): void
    {
        $magangLain = $this->buatMagang($this->buatLokasi(['name' => 'Kantor Uji 2']));
        $this->setWaktuWib('2026-11-16 16:30:00');

        $this->actingAs($magangLain)
            ->from(route('absen'))
            ->post(route('absen.checkout'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertSame(0, Attendance::where('user_id', $magangLain->id)->count());
    }
}