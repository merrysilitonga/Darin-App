<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Entri logbook di tanggal terakhir bulan harus ikut tampil, terkirim, dan terkunci.
 */
class LogbookAkhirBulanTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-12-01 09:00:00');
        $this->magang = $this->buatMagang();
    }

    private function entri(string $tanggal, string $status = 'Draft'): Logbook
    {
        return Logbook::create([
            'user_id' => $this->magang->id,
            'date' => $tanggal,
            'topic' => 'Topik ' . $tanggal,
            'description' => 'Deskripsi uji',
            'status' => $status,
        ]);
    }

    public function test_entri_tanggal_terakhir_bulan_tampil_di_daftar(): void
    {
        $this->entri('2026-11-30');

        $this->actingAs($this->magang)
            ->get(route('logbook', ['bulan' => '2026-11']))
            ->assertOk()
            ->assertSee('30/11/2026');
    }

    public function test_entri_tanggal_terakhir_bulan_ikut_dikirim(): void
    {
        $pertama = $this->entri('2026-11-02');
        $terakhir = $this->entri('2026-11-30');

        $this->actingAs($this->magang)
            ->from(route('logbook', ['bulan' => '2026-11']))
            ->post(route('logbook.submit-month'), ['bulan' => '2026-11'])
            ->assertSessionHas('success');

        $this->assertSame('Menunggu', $pertama->fresh()->status);
        $this->assertSame('Menunggu', $terakhir->fresh()->status);
    }

    public function test_bulan_terkunci_jika_hanya_entri_terakhir_yang_menunggu(): void
    {
        $this->entri('2026-11-02');
        $this->entri('2026-11-30', 'Menunggu');

        $this->actingAs($this->magang)
            ->from(route('logbook', ['bulan' => '2026-11']))
            ->post(route('logbook.submit-month'), ['bulan' => '2026-11'])
            ->assertSessionHas('error');
    }
}
