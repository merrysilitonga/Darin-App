<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Kalender hari libur: rentang tanggal, impor CSV, kalender bulanan, info peserta.
 */
class KalenderHariLiburTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 07:45:00'); // Senin
    }

    private function buatAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function dataLibur(array $override = []): array
    {
        return array_merge([
            'date' => '2026-12-24',
            'name' => 'Cuti Bersama Natal',
            'type' => 'Khusus ESDM',
            'description' => 'Cuti bersama',
            'is_active' => '1',
        ], $override);
    }

    private function jumlahLiburPada(string $tanggal): int
    {
        return Holiday::whereDate('date', $tanggal)->count();
    }

    private function fileCsv(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('libur.csv', $isi);
    }

    /*
    |--------------------------------------------------------------------------
    | INPUT RENTANG TANGGAL
    |--------------------------------------------------------------------------
    */

    public function test_admin_dapat_menambah_satu_tanggal_libur(): void
    {
        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.store'), $this->dataLibur())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, $this->jumlahLiburPada('2026-12-24'));
    }

    public function test_tanggal_tunggal_yang_sudah_ada_ditolak(): void
    {
        Holiday::create($this->dataLibur());

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.store'), $this->dataLibur(['name' => 'Duplikat']))
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('holidays', 1);
    }

    public function test_rentang_tanggal_membuat_satu_data_per_hari_dan_melewati_yang_sudah_ada(): void
    {
        Holiday::create($this->dataLibur([
            'date' => '2026-12-25',
            'name' => 'Hari Raya Natal',
            'type' => 'Nasional',
        ]));

        // Kamis 24 s.d. Minggu 27 Desember, akhir pekan tetap dicatat.
        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.store'), $this->dataLibur([
                'end_date' => '2026-12-27',
                'skip_weekend' => '0',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('holidays', 4);
        $this->assertSame(
            'Hari Raya Natal',
            Holiday::whereDate('date', '2026-12-25')->value('name')
        );
        $this->assertSame(1, $this->jumlahLiburPada('2026-12-27'));
    }

    public function test_rentang_tanggal_dapat_melewati_sabtu_dan_minggu(): void
    {
        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.store'), $this->dataLibur([
                'end_date' => '2026-12-28', // Kamis–Senin
                'skip_weekend' => '1',
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('holidays', 3); // 24, 25, 28
        $this->assertSame(0, $this->jumlahLiburPada('2026-12-26'));
        $this->assertSame(0, $this->jumlahLiburPada('2026-12-27'));
    }

    public function test_rentang_tanggal_lebih_dari_31_hari_ditolak(): void
    {
        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.store'), $this->dataLibur([
                'date' => '2026-12-01',
                'end_date' => '2027-01-15',
            ]))
            ->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('holidays', 0);
    }

    public function test_tanggal_selesai_sebelum_tanggal_mulai_ditolak(): void
    {
        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.store'), $this->dataLibur([
                'end_date' => '2026-12-20',
            ]))
            ->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('holidays', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | IMPOR CSV
    |--------------------------------------------------------------------------
    */

    public function test_impor_csv_valid_menyimpan_data_dan_melewati_tanggal_yang_sudah_ada(): void
    {
        Holiday::create($this->dataLibur(['date' => '2026-12-25', 'name' => 'Natal']));

        $csv = "\xEF\xBB\xBF" . "tanggal;nama;jenis;keterangan;aktif\n"
            . "2026-08-17;Hari Kemerdekaan;Nasional;;1\n"
            . "24/12/2026;Cuti Bersama Natal;Khusus ESDM;Cuti bersama;\n"
            . "2026-12-25;Natal;Nasional;;1\n"
            . "\n"
            . "2026-12-31;Libur Akhir Tahun;khusus esdm;;0\n";

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.import'), ['file' => $this->fileCsv($csv)])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('holidays', 4);
        $this->assertSame('Khusus ESDM', Holiday::whereDate('date', '2026-12-24')->value('type'));
        $this->assertFalse((bool) Holiday::whereDate('date', '2026-12-31')->value('is_active'));
    }

    public function test_impor_csv_dengan_pemisah_koma_tanpa_header(): void
    {
        $csv = "2026-08-17,Hari Kemerdekaan,Nasional,\"Libur, nasional\",1\n";

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.import'), ['file' => $this->fileCsv($csv)])
            ->assertSessionHas('success');

        $this->assertSame('Libur, nasional', Holiday::whereDate('date', '2026-08-17')->value('description'));
    }

    public function test_impor_csv_dengan_baris_tidak_valid_dibatalkan_seluruhnya(): void
    {
        $csv = "tanggal;nama;jenis\n"
            . "2026-08-17;Hari Kemerdekaan;Nasional\n"
            . "31/02/2026;Tanggal Salah;Nasional\n"
            . "2026-12-25;;Nasional\n"
            . "2026-12-26;Jenis Salah;Regional\n"
            . "2026-08-17;Duplikat;Nasional\n";

        $response = $this->actingAs($this->buatAdmin())
            ->post(route('admin.holidays.import'), ['file' => $this->fileCsv($csv)]);

        $response->assertSessionHasErrorsIn('import');
        $this->assertCount(4, session('errors')->getBag('import')->all());
        $this->assertDatabaseCount('holidays', 0);
    }

    public function test_template_csv_dapat_diunduh(): void
    {
        $response = $this->actingAs($this->buatAdmin())
            ->get(route('admin.holidays.template'));

        $response->assertOk();
        $this->assertStringContainsString('tanggal;nama;jenis;keterangan;aktif', $response->getContent());
    }

    public function test_magang_tidak_dapat_mengimpor_hari_libur(): void
    {
        $this->actingAs($this->buatMagang($this->buatLokasi()))
            ->post(route('admin.holidays.import'), [
                'file' => $this->fileCsv("2026-08-17;Hari Kemerdekaan;Nasional\n"),
            ]);

        $this->assertDatabaseCount('holidays', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | KALENDER BULANAN
    |--------------------------------------------------------------------------
    */

    public function test_halaman_admin_menampilkan_kalender_bulanan(): void
    {
        Holiday::create($this->dataLibur());

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.holidays', ['tahun' => 2026, 'bulan' => 12]))
            ->assertOk()
            ->assertSee('Kalender Desember 2026')
            ->assertSee('Cuti Bersama Natal');
    }

    /*
    |--------------------------------------------------------------------------
    | INFO LIBUR UNTUK PESERTA
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_magang_menampilkan_libur_hari_ini_dan_mendatang(): void
    {
        Holiday::create($this->dataLibur(['date' => '2026-11-16', 'name' => 'Libur Khusus Kantor']));
        Holiday::create($this->dataLibur(['date' => '2026-11-20', 'name' => 'Libur Mendatang']));
        Holiday::create($this->dataLibur(['date' => '2026-11-21', 'name' => 'Libur Nonaktif', 'is_active' => '0']));

        $this->actingAs($this->buatMagang($this->buatLokasi()))
            ->get(route('dashboard.magang'))
            ->assertOk()
            ->assertSee('Hari ini libur: Libur Khusus Kantor')
            ->assertSee('Libur Mendatang')
            ->assertDontSee('Libur Nonaktif');
    }

    public function test_halaman_absen_menampilkan_info_libur_hari_ini(): void
    {
        Holiday::create($this->dataLibur(['date' => '2026-11-16', 'name' => 'Libur Khusus Kantor']));

        $this->actingAs($this->buatMagang($this->buatLokasi()))
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Hari ini libur: Libur Khusus Kantor');
    }
}
