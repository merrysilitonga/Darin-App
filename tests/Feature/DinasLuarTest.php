<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Dinas Luar: absen selfie + GPS di luar radius kantor dengan tujuan dinas,
 * tercatat berlabel "Dinas Luar" dan dihitung hadir.
 */
class DinasLuarTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();
        // Senin, 16 November 2026.
        $this->setWaktuWib('2026-11-16 07:45:00');

        $this->magang = $this->buatMagang($this->buatLokasi());
    }

    private function absenDinas(array $override = [])
    {
        return $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen($this->koordinatLuarRadius() + array_merge([
                'work_location' => 'Dinas Luar',
                'tujuan_dinas' => 'Rapat di Dinas ESDM Jawa Barat, Bandung',
            ], $override)));
    }

    public function test_pilihan_dinas_luar_tampil_di_hari_biasa(): void
    {
        $this->actingAs($this->magang)
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Metode Kerja Hari Ini')
            ->assertSee('Dinas Luar - Ditugaskan di luar kantor')
            ->assertSee('id="tujuan_dinas"', false)
            // Hari biasa tidak menawarkan WFA.
            ->assertDontSee('WFA - Bekerja dari lokasi lain');
    }

    public function test_absen_dinas_luar_di_luar_radius_diterima_dan_tercatat(): void
    {
        $this->absenDinas()
            ->assertRedirect(route('absen'))
            ->assertSessionHas('success');

        $absen = Attendance::firstOrFail();
        $this->assertSame('Dinas Luar', $absen->work_location);
        $this->assertSame('Hadir', $absen->status);
        $this->assertSame('Rapat di Dinas ESDM Jawa Barat, Bandung', $absen->tujuanDinas());
        $this->assertGreaterThan(100, $absen->distance_meters);
    }

    public function test_tujuan_dinas_wajib_diisi(): void
    {
        $this->absenDinas(['tujuan_dinas' => ''])->assertSessionHasErrors('tujuan_dinas');

        $this->assertSame(0, Attendance::count());
    }

    public function test_tanpa_dinas_luar_di_luar_radius_tetap_ditolak(): void
    {
        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen($this->koordinatLuarRadius()))
            ->assertSessionHas('error');

        // WFA hanya untuk hari Jumat.
        $this->absenDinas(['work_location' => 'WFA'])->assertSessionHasErrors('work_location');

        $this->assertSame(0, Attendance::count());
    }

    public function test_dinas_luar_juga_bisa_dipilih_hari_jumat(): void
    {
        $this->setWaktuWib('2026-11-20 07:45:00'); // Jumat

        $this->absenDinas()->assertSessionHas('success');

        $this->assertSame('Dinas Luar', Attendance::firstOrFail()->work_location);
    }

    public function test_absen_pulang_dinas_luar_tidak_dibatasi_radius(): void
    {
        $this->absenDinas()->assertSessionHas('success');

        $this->setWaktuWib('2026-11-16 16:30:00');

        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.checkout'), $this->dataAbsen($this->koordinatLuarRadius()))
            ->assertSessionHas('success');

        $this->assertNotNull(Attendance::firstOrFail()->clock_out);
    }

    public function test_label_dinas_luar_tampil_di_riwayat_dan_rekap(): void
    {
        $this->absenDinas()->assertSessionHas('success');

        $this->actingAs($this->magang)
            ->get(route('riwayat.absen', ['bulan' => '2026-11']))
            ->assertOk()
            ->assertSee('Dinas Luar')
            ->assertSee('Rapat di Dinas ESDM Jawa Barat, Bandung');

        $this->actingAs($this->magang)
            ->get(route('rekap.saya'))
            ->assertOk()
            ->assertSee('Rapat di Dinas ESDM Jawa Barat, Bandung');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.attendance'))
            ->assertOk()
            ->assertSee('Dinas Luar')
            ->assertSee('Rapat di Dinas ESDM Jawa Barat, Bandung');
    }

    public function test_hari_izin_disetujui_kamera_tetap_tampil_hanya_dinas_luar(): void
    {
        LeaveRequest::create([
            'user_id' => $this->magang->id,
            'request_type' => 'Izin',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-17',
            'reason' => 'Urusan keluarga',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Disetujui',
        ]);

        $this->actingAs($this->magang)
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Hari Ini Anda Tercatat Izin')
            ->assertSee('id="formAbsen"', false)
            ->assertSee('id="video"', false)
            ->assertSee('Dinas Luar - Ditugaskan di luar kantor')
            ->assertDontSee('Di Kantor (wajib dalam radius)')
            ->assertSee('Tidak perlu (Izin)');

        // Absen biasa (bukan Dinas Luar) tetap ditolak.
        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('error');
        $this->assertSame(0, Attendance::count());

        // Dinas Luar diterima dan menunggu persetujuan Mentor.
        $this->absenDinas()->assertSessionHas('success');
        $this->assertSame('Menunggu', Attendance::firstOrFail()->dinas_status);
    }

    public function test_laporan_pdf_tetap_bisa_diunduh_dengan_data_dinas_luar(): void
    {
        $this->absenDinas()->assertSessionHas('success');

        // Tujuan dinas tercantum di template PDF Laporan Akhir & Rekap Admin.
        $html = view('recap.pdf', app(\App\Services\InternRecapService::class)->build($this->magang->fresh()))->render();
        $this->assertStringContainsString('Dinas Luar:', $html);
        $this->assertStringContainsString('Rapat di Dinas ESDM Jawa Barat, Bandung', $html);

        $this->actingAs($this->magang)
            ->get(route('rekap.saya.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.attendance.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
