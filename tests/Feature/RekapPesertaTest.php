<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\InternRecapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Rekap kehadiran per peserta: perhitungan, hak akses, dan unduhan PDF.
 */
class RekapPesertaTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00'); // Senin
    }

    private function buatAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function buatMentor(): User
    {
        return User::factory()->create(['role' => 'mentor']);
    }

    private function absen(User $magang, string $tanggal, string $status): void
    {
        Attendance::create([
            'user_id' => $magang->id,
            'date' => $tanggal,
            'clock_in' => $status === 'Terlambat' ? '08:30:00' : '07:50:00',
            'clock_out' => '16:05:00',
            'status' => $status,
            'work_location' => 'WFO',
        ]);
    }

    private function izin(User $magang, string $tanggal, string $jenis, string $status = 'Disetujui'): void
    {
        LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => $jenis,
            'start_date' => $tanggal,
            'end_date' => $tanggal,
            'reason' => 'Keperluan uji',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => $status,
        ]);
    }

    /**
     * Periode 1 Nov 2026 – 31 Des 2026, hari ini Senin 16 Nov:
     * 2 Nov hadir, 3 Nov terlambat, 4 Nov izin, 5 Nov sakit, 6 Nov libur,
     * 9–13 Nov tanpa keterangan (alfa), 16 Nov belum absen.
     */
    private function siapkanData(?User $mentor = null): User
    {
        $magang = $this->buatMagang(null, [
            'name' => 'Sinta Peserta',
            'mentor_id' => $mentor?->id,
        ]);

        $this->absen($magang, '2026-11-02', 'Hadir');
        $this->absen($magang, '2026-11-03', 'Terlambat');
        $this->izin($magang, '2026-11-04', 'Izin');
        $this->izin($magang, '2026-11-05', 'Sakit');
        $this->izin($magang, '2026-11-09', 'Izin', 'Ditolak');

        Holiday::create([
            'date' => '2026-11-06',
            'name' => 'Libur Uji',
            'type' => 'Khusus ESDM',
            'is_active' => true,
        ]);

        return $magang;
    }

    /*
    |--------------------------------------------------------------------------
    | PERHITUNGAN
    |--------------------------------------------------------------------------
    */

    public function test_perhitungan_rekap_sesuai_aturan_hari_kerja(): void
    {
        $magang = $this->siapkanData();

        $rekap = app(InternRecapService::class)->build($magang);
        $ringkasan = $rekap['summary'];

        $this->assertSame('Berlangsung', $rekap['periodState']);
        $this->assertSame(9, $ringkasan['effective_workdays']);
        $this->assertSame(1, $ringkasan['hadir']);
        $this->assertSame(1, $ringkasan['terlambat']);
        $this->assertSame(1, $ringkasan['izin']);
        $this->assertSame(1, $ringkasan['sakit']);
        $this->assertSame(5, $ringkasan['alfa']); // izin ditolak 9 Nov tetap alfa
        $this->assertSame(1, $ringkasan['libur']);
        $this->assertSame(22, $ringkasan['attendance_rate']);

        $hariIni = collect($rekap['days'])->last();
        $this->assertSame('Belum Absen', $hariIni['status']);
    }

    public function test_rekap_periode_belum_dimulai_tidak_menghitung_apa_pun(): void
    {
        $magang = $this->buatMagang(null, [
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-31',
        ]);

        $rekap = app(InternRecapService::class)->build($magang);

        $this->assertSame('Belum dimulai', $rekap['periodState']);
        $this->assertNull($rekap['countUntil']);
        $this->assertSame(0, $rekap['summary']['effective_workdays']);
    }

    /*
    |--------------------------------------------------------------------------
    | HAK AKSES & TAMPILAN
    |--------------------------------------------------------------------------
    */

    public function test_admin_dapat_melihat_rekap_peserta(): void
    {
        $magang = $this->siapkanData();

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.interns.recap', $magang))
            ->assertOk()
            ->assertSee('Rekap Magang: Sinta Peserta')
            ->assertSee('Libur Uji');
    }

    public function test_admin_dapat_mengunduh_pdf_rekap(): void
    {
        $magang = $this->siapkanData();

        $response = $this->actingAs($this->buatAdmin())
            ->get(route('admin.interns.recap-pdf', $magang));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }

    public function test_rekap_untuk_akun_bukan_magang_tidak_ditemukan(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('admin.interns.recap', $this->buatMentor()))
            ->assertNotFound();
    }

    public function test_mentor_dapat_melihat_rekap_mahasiswa_bimbingannya(): void
    {
        $mentor = $this->buatMentor();
        $magang = $this->siapkanData($mentor);

        $this->actingAs($mentor)
            ->get(route('mentor.interns.recap', $magang))
            ->assertOk()
            ->assertSee('Sinta Peserta');
    }

    public function test_mentor_tidak_dapat_melihat_rekap_mahasiswa_lain(): void
    {
        $magang = $this->siapkanData($this->buatMentor());

        $this->actingAs($this->buatMentor())
            ->get(route('mentor.interns.recap', $magang))
            ->assertForbidden();
    }

    public function test_magang_dapat_melihat_rekap_miliknya_sendiri(): void
    {
        $magang = $this->siapkanData();

        $this->actingAs($magang)
            ->get(route('rekap.saya'))
            ->assertOk()
            ->assertSee('Rekap Magang: Sinta Peserta');
    }

    public function test_magang_tidak_dapat_membuka_rekap_admin(): void
    {
        $magang = $this->siapkanData();

        $this->actingAs($magang)
            ->get(route('admin.interns.recap', $magang))
            ->assertForbidden();
    }
}
