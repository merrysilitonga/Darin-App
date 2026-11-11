<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Aturan bisnis absen masuk (POST /absen).
 * Senin 16 Nov 2026 dipakai sebagai hari kerja normal.
 */
class AbsensiMasukTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();
    }

    public function test_absen_masuk_dalam_radius_sebelum_jam_masuk_tercatat_hadir(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertRedirect(route('absen'))
            ->assertSessionHas('success');

        $absen = Attendance::where('user_id', $magang->id)->firstOrFail();

        $this->assertSame('Hadir', $absen->status);
        $this->assertSame(0, (int) $absen->distance_meters);
        Storage::disk('local')->assertExists($absen->photo_path);
    }

    public function test_absen_setelah_jam_masuk_tercatat_terlambat(): void
    {
        $this->setWaktuWib('2026-11-16 08:30:00');
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $magang->id,
            'status' => 'Terlambat',
        ]);
    }

    public function test_absen_di_luar_radius_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen($this->koordinatLuarRadius()))
            ->assertRedirect(route('absen'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_absen_jumat_wfa_di_luar_radius_diterima(): void
    {
        $this->setWaktuWib('2026-11-20 07:45:00'); // Jumat
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->post(route('absen.store'), $this->dataAbsen(
                $this->koordinatLuarRadius() + ['work_location' => 'WFA']
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $magang->id,
            'work_location' => 'WFA',
        ]);
    }

    public function test_absen_jumat_wfo_di_luar_radius_ditolak(): void
    {
        $this->setWaktuWib('2026-11-20 07:45:00'); // Jumat
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen(
                $this->koordinatLuarRadius() + ['work_location' => 'WFO']
            ))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_absen_pada_akhir_pekan_ditolak(): void
    {
        $this->setWaktuWib('2026-11-14 07:45:00'); // Sabtu
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_absen_pada_hari_libur_aktif_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi());

        Holiday::create([
            'date' => '2026-11-16',
            'name' => 'Libur Uji',
            'is_active' => true,
        ]);

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_absen_saat_izin_sudah_disetujui_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi());

        LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => 'Sakit',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-17',
            'reason' => 'Demam',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Disetujui',
        ]);

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_izin_yang_masih_menunggu_tidak_menghalangi_absen(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi());

        LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => 'Izin',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-16',
            'reason' => 'Urusan keluarga',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Menunggu',
        ]);

        $this->actingAs($magang)
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('success');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_absen_masuk_dua_kali_di_hari_yang_sama_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)->post(route('absen.store'), $this->dataAbsen());

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_absen_di_luar_periode_magang_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 07:45:00');
        $magang = $this->buatMagang($this->buatLokasi(), [
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-31',
        ]);

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_absen_melewati_batas_jam_masuk_ditolak(): void
    {
        $this->setWaktuWib('2026-11-16 12:30:00'); // batas masuk 12:00
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }
}