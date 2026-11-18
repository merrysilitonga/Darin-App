<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Penilaian akhir magang oleh mentor.
 */
class PenilaianMentorTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $mentor;
    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 10:00:00');

        $this->mentor = User::factory()->create(['role' => 'mentor']);
        $this->magang = $this->buatMagang(null, [
            'name' => "Sinta O'Brien",
            'university' => 'Universitas Bina Sarana Informatika',
            'mentor_id' => $this->mentor->id,
        ]);
    }

    private function nilai(array $override = []): array
    {
        return array_merge([
            'kedisiplinan' => 90,
            'kinerja_teknis' => 80,
            'sikap_etika' => 85,
            'laporan' => 75,
        ], $override);
    }

    public function test_mentor_melihat_mahasiswa_bimbingannya(): void
    {
        $this->actingAs($this->mentor)
            ->get(route('mentor.assessment'))
            ->assertOk()
            ->assertSee('1 Mahasiswa Bimbingan')
            ->assertSee('Belum Dinilai')
            ->assertSee('Universitas Bina Sarana Informatika')
            // Nama dengan tanda kutip aman di dalam JavaScript tombol.
            ->assertSee("activeStudentName = 'Sinta O\\u0027Brien'", false);
    }

    public function test_mentor_dapat_menyimpan_dan_mengubah_nilai(): void
    {
        $this->actingAs($this->mentor)
            ->post(route('mentor.assessment.store', $this->magang->id), $this->nilai())
            ->assertSessionHas('success');

        $this->actingAs($this->mentor)
            ->post(route('mentor.assessment.store', $this->magang->id), $this->nilai(['laporan' => 95]))
            ->assertSessionHas('success');

        $this->assertSame(1, Assessment::count());

        $nilai = Assessment::first();
        $this->assertSame($this->magang->id, (int) $nilai->user_id);
        $this->assertSame($this->mentor->id, (int) $nilai->mentor_id);
        $this->assertEqualsWithDelta(95.0, $nilai->laporan, 0.001);
        $this->assertNotNull($nilai->tanggal_penilaian);

        // 90*.25 + 80*.35 + 85*.20 + 95*.20 = 86.5 → A
        $this->actingAs($this->mentor)
            ->get(route('mentor.assessment'))
            ->assertSee('86.5 (A)');
    }

    public function test_nilai_di_luar_0_sampai_100_ditolak(): void
    {
        $this->actingAs($this->mentor)
            ->post(route('mentor.assessment.store', $this->magang->id), $this->nilai(['kinerja_teknis' => 150]))
            ->assertSessionHasErrors('kinerja_teknis');

        $this->assertSame(0, Assessment::count());
    }

    public function test_mentor_tidak_dapat_menilai_mahasiswa_lain(): void
    {
        $lain = $this->buatMagang(null, [
            'mentor_id' => User::factory()->create(['role' => 'mentor'])->id,
        ]);

        $this->actingAs($this->mentor)
            ->post(route('mentor.assessment.store', $lain->id), $this->nilai())
            ->assertNotFound();

        $this->assertSame(0, Assessment::count());
    }

    public function test_rapor_pdf_dapat_diunduh_setelah_dinilai(): void
    {
        // Belum dinilai → belum ada rapor.
        $this->actingAs($this->mentor)
            ->get(route('mentor.assessment.pdf', $this->magang->id))
            ->assertNotFound();

        $this->actingAs($this->mentor)
            ->post(route('mentor.assessment.store', $this->magang->id), $this->nilai());

        $response = $this->actingAs($this->mentor)
            ->get(route('mentor.assessment.pdf', $this->magang->id));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));

        // Isi rapor: 90*.25 + 80*.35 + 85*.20 + 75*.20 = 82,5 → B (Baik).
        $html = view('mentor.assessment-pdf', [
            'mentor' => $this->mentor,
            'student' => $this->magang->fresh(['division', 'location']),
            'assessment' => Assessment::first(),
        ])->render();

        $this->assertStringContainsString('Rapor Penilaian Akhir Magang', $html);
        $this->assertStringContainsString('82,50', $html);
        $this->assertStringContainsString('B (Baik)', $html);
        $this->assertStringContainsString('Universitas Bina Sarana Informatika', $html);
    }

    public function test_mentor_tidak_dapat_mengunduh_rapor_mahasiswa_lain(): void
    {
        $lain = $this->buatMagang(null, [
            'mentor_id' => User::factory()->create(['role' => 'mentor'])->id,
        ]);

        Assessment::create([
            'user_id' => $lain->id,
            'kedisiplinan' => 80,
            'kinerja_teknis' => 80,
            'sikap_etika' => 80,
            'laporan' => 80,
        ]);

        $this->actingAs($this->mentor)
            ->get(route('mentor.assessment.pdf', $lain->id))
            ->assertNotFound();
    }
}
