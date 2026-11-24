<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Status bulan di halaman Logbook mahasiswa harus sama dengan status entri
 * (menunggu / disetujui), bukan selalu "sedang diperiksa" saat bulan terkunci.
 */
class StatusLogbookBulananTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-24 09:00:00');
        $this->magang = $this->buatMagang();
    }

    private function entri(string $status): Logbook
    {
        return Logbook::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-18',
            'topic' => 'Topik uji',
            'description' => 'Deskripsi uji',
            'status' => $status,
        ]);
    }

    private function halaman()
    {
        return $this->actingAs($this->magang)
            ->get(route('logbook', ['bulan' => '2026-11']))
            ->assertOk();
    }

    public function test_bulan_yang_sudah_disetujui_tidak_tampil_sedang_diperiksa(): void
    {
        $this->entri('Disetujui');

        $this->halaman()
            ->assertSee('Logbook November 2026 sudah disetujui Mentor.')
            ->assertSee('Sudah Disetujui')
            ->assertDontSee('sedang diperiksa')
            ->assertDontSee('Menunggu Pemeriksaan');
    }

    public function test_bulan_yang_masih_menunggu_tampil_sedang_diperiksa(): void
    {
        $this->entri('Menunggu');

        $this->halaman()
            ->assertSee('Logbook November 2026 sedang diperiksa.')
            ->assertSee('Menunggu Pemeriksaan')
            ->assertDontSee('sudah disetujui Mentor.');
    }
}
