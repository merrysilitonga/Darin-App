<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Nilai akhir magang tampil di Laporan Akhir dan rapor PDF bisa diunduh
 * oleh peserta sendiri, mentornya, dan Admin/HR.
 */
class NilaiAkhirMahasiswaTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $mentor;
    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');

        $this->mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Nayla Mentor']);
        $this->magang = $this->buatMagang(null, ['mentor_id' => $this->mentor->id]);
    }

    private function beriNilai(): Assessment
    {
        // Nilai akhir = 80×25% + 90×35% + 85×20% + 70×20% = 82,50 → B.
        return Assessment::create([
            'user_id' => $this->magang->id,
            'mentor_id' => $this->mentor->id,
            'kedisiplinan' => 80,
            'kinerja_teknis' => 90,
            'sikap_etika' => 85,
            'laporan' => 70,
            'tanggal_penilaian' => '2026-11-15 10:00:00',
        ]);
    }

    public function test_belum_dinilai_menampilkan_keterangan_dan_rapor_tidak_tersedia(): void
    {
        $this->actingAs($this->magang)
            ->get(route('rekap.saya'))
            ->assertOk()
            ->assertSee('Nilai Akhir Magang')
            ->assertSee('Belum dinilai')
            ->assertDontSee('Unduh Rapor Nilai');

        $this->actingAs($this->magang)
            ->get(route('rekap.saya.rapor'))
            ->assertNotFound();
    }

    public function test_peserta_melihat_nilai_akhir_dan_mengunduh_rapor(): void
    {
        $this->beriNilai();

        $this->actingAs($this->magang)
            ->get(route('rekap.saya'))
            ->assertOk()
            ->assertSee('Dinilai oleh Nayla Mentor')
            ->assertSee('82,50')
            ->assertSee('Kinerja Teknis')
            ->assertSee('(35%)')
            ->assertSee('Unduh Rapor Nilai (PDF)')
            ->assertSee(route('rekap.saya.rapor'), false);

        $this->actingAs($this->magang)
            ->get(route('rekap.saya.rapor'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_peserta_yang_magangnya_selesai_tetap_bisa_mengunduh_rapor(): void
    {
        $this->beriNilai();
        $this->magang->update(['start_date' => '2026-09-01', 'end_date' => '2026-10-31']);

        $this->actingAs($this->magang->fresh())
            ->get(route('rekap.saya.rapor'))
            ->assertOk();
    }

    public function test_mentor_dan_admin_melihat_nilai_di_rekap_peserta(): void
    {
        $this->beriNilai();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->mentor)
            ->get(route('mentor.interns.recap', $this->magang))
            ->assertOk()
            ->assertSee('82,50')
            ->assertSee(route('mentor.assessment.pdf', $this->magang->id), false);

        $this->actingAs($admin)
            ->get(route('admin.interns.recap', $this->magang))
            ->assertOk()
            ->assertSee('82,50')
            ->assertSee(route('admin.interns.rapor', $this->magang), false);

        $this->actingAs($admin)
            ->get(route('admin.interns.rapor', $this->magang))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
