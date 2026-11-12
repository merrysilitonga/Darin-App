<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AdminChartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Grafik dashboard admin: ringkasan hari ini, tren harian, persentase per divisi.
 * Hari ini Senin 16 Nov 2026; periode magang 1 Nov – 31 Des 2026; 6 Nov libur.
 */
class GrafikDashboardAdminTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 10:00:00');
    }

    private function absen(User $magang, string $tanggal, string $status = 'Hadir'): void
    {
        Attendance::create([
            'user_id' => $magang->id,
            'date' => $tanggal,
            'clock_in' => '07:50:00',
            'status' => $status,
        ]);
    }

    private function siapkanData(): void
    {
        $it = Division::create(['name' => 'Teknologi Informasi']);
        $keuangan = Division::create(['name' => 'Keuangan']);

        $rajin = $this->buatMagang(null, ['division_id' => $it->id]);
        $jarang = $this->buatMagang(null, ['division_id' => $keuangan->id]);

        // Sudah selesai sebelum rentang grafik → tidak dihitung.
        $this->buatMagang(null, ['start_date' => '2026-08-01', 'end_date' => '2026-09-30']);

        Holiday::create(['date' => '2026-11-06', 'name' => 'Libur Uji', 'type' => 'Nasional', 'is_active' => true]);

        foreach (['02', '03', '04', '05', '09', '10', '11', '12', '13', '16'] as $hari) {
            $this->absen($rajin, "2026-11-{$hari}");
        }

        $this->absen($jarang, '2026-11-02', 'Terlambat');

        LeaveRequest::create([
            'user_id' => $jarang->id,
            'request_type' => 'Izin',
            'start_date' => '2026-11-03',
            'end_date' => '2026-11-03',
            'reason' => 'Uji',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Disetujui',
        ]);
    }

    private function hari(array $grafik, string $tanggal): array
    {
        return collect($grafik['trend'])
            ->first(fn ($hari) => $hari['date']->toDateString() === $tanggal);
    }

    public function test_ringkasan_hari_ini(): void
    {
        $this->siapkanData();

        $hariIni = app(AdminChartService::class)->build()['today'];

        $this->assertSame(2, $hariIni['active']);
        $this->assertSame(1, $hariIni['hadir']);
        $this->assertSame(1, $hariIni['belum']);
        $this->assertSame(0, $hariIni['alfa']);
        $this->assertSame(100, $hariIni['rate']);
    }

    public function test_tren_harian_menghitung_status_dan_hari_libur(): void
    {
        $this->siapkanData();

        $grafik = app(AdminChartService::class)->build();

        $this->assertCount(30, $grafik['trend']);

        $senin = $this->hari($grafik, '2026-11-02');
        $this->assertSame(1, $senin['hadir']);
        $this->assertSame(1, $senin['terlambat']);

        $selasa = $this->hari($grafik, '2026-11-03');
        $this->assertSame(1, $selasa['izin']);
        $this->assertSame(0, $selasa['alfa']);

        $rabu = $this->hari($grafik, '2026-11-04');
        $this->assertSame(1, $rabu['alfa']);
        $this->assertSame(50, $rabu['rate']);

        $this->assertSame('Libur: Libur Uji', $this->hari($grafik, '2026-11-06')['off']);
        $this->assertSame('Akhir pekan', $this->hari($grafik, '2026-11-15')['off']);

        // Sebelum periode magang dimulai tidak ada peserta aktif.
        $this->assertSame(0, $this->hari($grafik, '2026-10-30')['active']);
    }

    public function test_persentase_per_divisi_bulan_berjalan(): void
    {
        $this->siapkanData();

        $divisi = collect(app(AdminChartService::class)->build()['divisions'])->keyBy('name');

        // 9 hari kerja s.d. 13 Nov + hari ini (sudah absen) = 10 hari, hadir semua.
        $this->assertSame(100, $divisi['Teknologi Informasi']['rate']);
        $this->assertSame(10, $divisi['Teknologi Informasi']['workdays']);

        // 9 hari kerja (hari ini belum absen tidak dihitung), hadir 1.
        $this->assertSame(11, $divisi['Keuangan']['rate']);
        $this->assertSame(9, $divisi['Keuangan']['workdays']);

        // Urut dari persentase tertinggi.
        $this->assertSame('Teknologi Informasi', array_key_first($divisi->all()));
    }

    public function test_dashboard_admin_menampilkan_grafik(): void
    {
        $this->siapkanData();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tren Kehadiran 30 Hari Terakhir')
            ->assertSee('Kehadiran per Divisi')
            ->assertSee('Teknologi Informasi');
    }

    public function test_dashboard_tetap_tampil_tanpa_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada data kehadiran bulan ini.');
    }
}
