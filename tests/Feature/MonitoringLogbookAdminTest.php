<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

class MonitoringLogbookAdminTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 10:00:00');
    }

    private function logbook(User $magang, string $tanggal, string $topik, string $status): void
    {
        Logbook::create([
            'user_id' => $magang->id,
            'date' => $tanggal,
            'topic' => $topik,
            'description' => 'Deskripsi',
            'status' => $status,
        ]);
    }

    public function test_admin_melihat_logbook_bulan_terpilih_tanpa_draft(): void
    {
        $magang = $this->buatMagang(null, ['name' => 'Rina Peserta']);

        $this->logbook($magang, '2026-11-10', 'Topik November', 'Menunggu');
        $this->logbook($magang, '2026-11-11', 'Topik Draft', 'Draft');
        $this->logbook($magang, '2026-10-10', 'Topik Oktober', 'Disetujui');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.logbooks', ['bulan' => '2026-11']))
            ->assertOk()
            ->assertSee('Monitoring Logbook Peserta')
            ->assertSee('Rina Peserta')
            ->assertSee('Topik November')
            ->assertDontSee('Topik Draft')
            ->assertDontSee('Topik Oktober');
    }

    public function test_hr_dapat_membuka_dan_magang_tidak(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr']))
            ->get(route('admin.logbooks'))
            ->assertOk();

        $this->actingAs($this->buatMagang())
            ->get(route('admin.logbooks'))
            ->assertForbidden();
    }
}
