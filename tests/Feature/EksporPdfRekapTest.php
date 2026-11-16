<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Regresi: tombol Ekspor PDF di Rekap Absensi admin (view sempat hilang → error 500).
 */
class EksporPdfRekapTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    public function test_admin_dapat_mengunduh_pdf_rekap_absensi(): void
    {
        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.export-pdf', ['month' => '2026-11']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }
}