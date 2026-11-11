<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Hak akses, periode magang, rekap admin, ekspor, dan halaman mentor.
 */
class AksesDanRekapTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 07:45:00');
    }

    /*
    |--------------------------------------------------------------------------
    | PERIODE MAGANG BERAKHIR (mode baca saja)
    |--------------------------------------------------------------------------
    */

    public function test_magang_yang_periodenya_berakhir_tidak_bisa_absen(): void
    {
        $magang = $this->buatMagang($this->buatLokasi(), [
            'start_date' => '2026-09-01',
            'end_date' => '2026-11-15', // kemarin
        ]);

        $this->actingAs($magang)
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertRedirect(route('dashboard.magang'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_magang_yang_periodenya_berakhir_masih_bisa_melihat_riwayat(): void
    {
        $magang = $this->buatMagang($this->buatLokasi(), [
            'start_date' => '2026-09-01',
            'end_date' => '2026-11-15',
        ]);

        $this->actingAs($magang)
            ->get(route('riwayat.absen'))
            ->assertOk();
    }

    public function test_pengajuan_izin_di_luar_periode_magang_ditolak(): void
    {
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->from(route('izin.create'))
            ->post(route('izin.store'), [
                'request_type' => 'Izin',
                'start_date' => '2027-01-05', // periode berakhir 31 Des 2026
                'end_date' => '2027-01-06',
                'reason' => 'Urusan keluarga',
                'document' => UploadedFile::fake()->createWithContent(
                    'surat.pdf',
                    "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n"
                ),
            ])
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | REKAP & EKSPOR ADMIN
    |--------------------------------------------------------------------------
    */

    private function buatAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function buatAbsensiLengkap(User $magang): Attendance
    {
        Storage::disk('local')->put('attendance/selfies/masuk.jpg', 'foto-masuk');
        Storage::disk('local')->put('attendance/selfies/pulang.jpg', 'foto-pulang');

        return Attendance::create([
            'user_id' => $magang->id,
            'date' => '2026-11-16',
            'clock_in' => '07:45:00',
            'clock_out' => '16:30:00',
            'status' => 'Hadir',
            'photo_path' => 'attendance/selfies/masuk.jpg',
            'clock_out_photo_path' => 'attendance/selfies/pulang.jpg',
            'latitude' => $this->kantorLat,
            'longitude' => $this->kantorLng,
            'location_name' => 'Kantor Uji',
            'distance_meters' => 0,
            'clock_out_distance_meters' => 5,
        ]);
    }

    public function test_halaman_rekap_absensi_admin_dapat_dibuka(): void
    {
        // Mencegah regresi bug: view admin sebelumnya berisi halaman mentor (error 500).
        $this->withoutVite();

        $magang = $this->buatMagang($this->buatLokasi(), ['name' => 'Budi Rekap']);
        $this->buatAbsensiLengkap($magang);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.attendance', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('Rekap Absensi')
            ->assertSee('Budi Rekap');
    }

    public function test_ekspor_csv_rekap_berisi_data_absensi(): void
    {
        $magang = $this->buatMagang($this->buatLokasi(), ['name' => 'Budi Ekspor']);
        $this->buatAbsensiLengkap($magang);

        $response = $this->actingAs($this->buatAdmin())
            ->get(route('admin.attendance.export-csv', ['month' => '2026-11']));

        $response->assertOk();

        $isi = $response->streamedContent();

        $this->assertStringContainsString('Budi Ekspor', $isi);
        $this->assertStringContainsString('Tepat Waktu', $isi);
    }

    public function test_admin_dapat_membuka_foto_absen_pulang(): void
    {
        $absen = $this->buatAbsensiLengkap($this->buatMagang($this->buatLokasi()));
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->get(route('admin.attendance.photo', ['attendance' => $absen, 'type' => 'pulang']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.attendance.photo', $absen))
            ->assertOk();
    }

    public function test_magang_tidak_dapat_membuka_rekap_admin(): void
    {
        $this->actingAs($this->buatMagang($this->buatLokasi()))
            ->get(route('admin.attendance'))
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | PENGATURAN JAM KERJA
    |--------------------------------------------------------------------------
    */

    public function test_jam_pulang_tidak_boleh_sama_dengan_batas_jam_masuk(): void
    {
        $this->actingAs($this->buatAdmin())
            ->from(route('admin.settings'))
            ->put(route('admin.settings.update'), [
                'clock_in_start' => '08:00',
                'clock_in_end' => '17:00',
                'friday_clock_in_end' => '11:00',
                'clock_out_time' => '17:00',
                'friday_clock_out_time' => '16:30',
            ])
            ->assertSessionHasErrors('clock_out_time');
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN MENTOR
    |--------------------------------------------------------------------------
    */

    public function test_halaman_kehadiran_mentor_aman_dari_xss_nama_pengguna(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $namaJahat = "x');alert(1);//";

        $magang = $this->buatMagang($this->buatLokasi(), [
            'name' => $namaJahat,
            'mentor_id' => $mentor->id,
        ]);

        LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => 'Izin',
            'start_date' => '2026-11-17',
            'end_date' => '2026-11-17',
            'reason' => 'Uji',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Menunggu',
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.attendance'))
            ->assertOk()
            ->assertSee('Setujui Pengajuan')
            // Tanda kutip mentah tidak boleh muncul di HTML (harus ter-escape).
            ->assertDontSee($namaJahat, false);
    }
}