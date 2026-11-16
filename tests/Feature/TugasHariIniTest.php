<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use App\Services\InternTodayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Kartu "Tugas Hari Ini" di dashboard peserta magang.
 * Jadwal uji: masuk 08.00, batas 12.00 (Jumat 11.00), pulang 16.00 (Jumat 16.30).
 */
class TugasHariIniTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
    }

    /**
     * Daftar tugas sebagai [key => item].
     */
    private function tugas(User $magang, string $waktuWib): array
    {
        $this->setWaktuWib($waktuWib);

        return collect(app(InternTodayService::class)->tasks($magang))
            ->keyBy('key')
            ->all();
    }

    private function absen(User $magang, string $tanggal, string $masuk = '07:45:00', ?string $pulang = null): void
    {
        Attendance::create([
            'user_id' => $magang->id,
            'date' => $tanggal,
            'clock_in' => $masuk,
            'clock_out' => $pulang,
            'status' => 'Hadir',
            'work_location' => 'WFO',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ABSEN MASUK
    |--------------------------------------------------------------------------
    */

    public function test_pagi_sebelum_absen_muncul_tugas_absen_masuk_dan_logbook(): void
    {
        $tugas = $this->tugas($this->buatMagang(), '2026-11-16 07:30:00');

        $this->assertSame('todo', $tugas['clock-in']['state']);
        $this->assertSame('Belum absen masuk', $tugas['clock-in']['title']);
        $this->assertStringContainsString('tepat waktu', $tugas['clock-in']['description']);
        $this->assertSame('todo', $tugas['logbook-today']['state']);
        $this->assertArrayNotHasKey('clock-out', $tugas);
    }

    public function test_sebelum_jam_enam_absen_belum_dibuka(): void
    {
        $tugas = $this->tugas($this->buatMagang(), '2026-11-16 05:30:00');

        $this->assertSame('Absen masuk belum dibuka', $tugas['clock-in']['title']);
    }

    public function test_lewat_jam_masuk_diberi_tahu_akan_terlambat(): void
    {
        $tugas = $this->tugas($this->buatMagang(), '2026-11-16 09:00:00');

        $this->assertSame('todo', $tugas['clock-in']['state']);
        $this->assertStringContainsString('terlambat', $tugas['clock-in']['description']);
    }

    public function test_lewat_batas_absen_masuk_ditandai_terlewat(): void
    {
        $tugas = $this->tugas($this->buatMagang(), '2026-11-16 13:00:00');

        $this->assertSame('missed', $tugas['clock-in']['state']);
        $this->assertArrayNotHasKey('clock-out', $tugas);
    }

    /*
    |--------------------------------------------------------------------------
    | ABSEN PULANG
    |--------------------------------------------------------------------------
    */

    public function test_setelah_absen_masuk_muncul_jadwal_absen_pulang(): void
    {
        $magang = $this->buatMagang();
        $this->absen($magang, '2026-11-16');

        $tugas = $this->tugas($magang, '2026-11-16 10:00:00');

        $this->assertSame('done', $tugas['clock-in']['state']);
        $this->assertSame('todo', $tugas['clock-out']['state']);
        $this->assertSame('Absen pulang mulai pukul 16.00 WIB', $tugas['clock-out']['title']);
    }

    public function test_jam_pulang_tiba_tetapi_belum_absen_pulang(): void
    {
        $magang = $this->buatMagang();
        $this->absen($magang, '2026-11-16');

        $tugas = $this->tugas($magang, '2026-11-16 16:30:00');

        $this->assertSame('Belum absen pulang', $tugas['clock-out']['title']);
        $this->assertNotNull($tugas['clock-out']['url']);
    }

    public function test_absen_pulang_selesai_ditandai_beres(): void
    {
        $magang = $this->buatMagang();
        $this->absen($magang, '2026-11-16', '07:45:00', '16:10:00');

        $tugas = $this->tugas($magang, '2026-11-16 16:30:00');

        $this->assertSame('done', $tugas['clock-out']['state']);
    }

    /*
    |--------------------------------------------------------------------------
    | HARI TANPA KEWAJIBAN
    |--------------------------------------------------------------------------
    */

    public function test_akhir_pekan_tidak_ada_tugas_absen(): void
    {
        $tugas = $this->tugas($this->buatMagang(), '2026-11-21 09:00:00'); // Sabtu

        $this->assertSame('info', $tugas['day-off']['state']);
        $this->assertArrayNotHasKey('clock-in', $tugas);
        $this->assertArrayNotHasKey('logbook-today', $tugas);
    }

    public function test_hari_libur_tidak_ada_tugas_absen(): void
    {
        Holiday::create([
            'date' => '2026-11-16',
            'name' => 'Libur Uji',
            'type' => 'Nasional',
            'is_active' => true,
        ]);

        $tugas = $this->tugas($this->buatMagang(), '2026-11-16 09:00:00');

        $this->assertStringContainsString('Libur Uji', $tugas['day-off']['title']);
        $this->assertArrayNotHasKey('clock-in', $tugas);
    }

    public function test_izin_disetujui_tidak_ada_tugas_absen(): void
    {
        $magang = $this->buatMagang();

        LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => 'Sakit',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-17',
            'reason' => 'Demam',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Disetujui',
        ]);

        $tugas = $this->tugas($magang, '2026-11-16 09:00:00');

        $this->assertStringContainsString('Sakit', $tugas['day-off']['title']);
        $this->assertArrayNotHasKey('clock-in', $tugas);
    }

    public function test_periode_magang_berakhir_tidak_ada_tugas(): void
    {
        $magang = $this->buatMagang(null, [
            'start_date' => '2026-09-01',
            'end_date' => '2026-11-15',
        ]);

        $this->assertSame([], $this->tugas($magang, '2026-11-16 09:00:00'));
    }

    /*
    |--------------------------------------------------------------------------
    | LOGBOOK & IZIN
    |--------------------------------------------------------------------------
    */

    public function test_logbook_hari_ini_terisi_dan_logbook_ditolak_perlu_revisi(): void
    {
        $magang = $this->buatMagang();

        Logbook::create([
            'user_id' => $magang->id,
            'date' => '2026-11-16',
            'topic' => 'Hari ini',
            'description' => 'Kegiatan uji',
            'status' => 'Draft',
        ]);

        Logbook::create([
            'user_id' => $magang->id,
            'date' => '2026-11-13',
            'topic' => 'Ditolak',
            'description' => 'Kegiatan uji',
            'status' => 'Ditolak',
        ]);

        $tugas = $this->tugas($magang, '2026-11-16 09:00:00');

        $this->assertSame('done', $tugas['logbook-today']['state']);
        $this->assertSame('1 logbook ditolak mentor', $tugas['logbook-rejected']['title']);
    }

    public function test_dashboard_menampilkan_kartu_tugas_hari_ini(): void
    {
        $this->setWaktuWib('2026-11-16 07:30:00');

        $this->actingAs($this->buatMagang())
            ->get(route('dashboard.magang'))
            ->assertOk()
            ->assertSee('Tugas Hari Ini')
            ->assertSee('Belum absen masuk')
            ->assertSee('Logbook hari ini belum diisi');
    }
}
