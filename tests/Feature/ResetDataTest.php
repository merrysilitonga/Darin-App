<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Feedback;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Reset Data: hanya admin, wajib password + HAPUS,
 * dan hanya menyentuh peserta yang magangnya sudah selesai.
 */
class ResetDataTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $admin;
    private User $mentor;
    private User $alumni;
    private User $aktif;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 10:00:00');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('rahasia123'),
        ]);

        $this->mentor = User::factory()->create(['role' => 'mentor']);

        // Periode berakhir kemarin → boleh direset.
        $this->alumni = $this->buatMagang(null, [
            'mentor_id' => $this->mentor->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-11-15',
            'photo' => 'profiles/alumni.jpg',
        ]);

        // Masih aktif → tidak boleh tersentuh.
        $this->aktif = $this->buatMagang(null, ['mentor_id' => $this->mentor->id]);

        Storage::disk('public')->put('profiles/alumni.jpg', 'x');

        foreach ([$this->alumni, $this->aktif] as $magang) {
            $this->isiData($magang);
        }
    }

    private function isiData(User $magang): void
    {
        $masuk = "attendance/selfies/masuk-{$magang->id}.jpg";
        $pulang = "attendance/selfies/pulang-{$magang->id}.jpg";
        $dokumen = "surat-izin/izin-{$magang->id}.pdf";

        Storage::disk('local')->put($masuk, 'x');
        Storage::disk('local')->put($pulang, 'x');
        Storage::disk('local')->put($dokumen, 'x');

        Attendance::create([
            'user_id' => $magang->id,
            'date' => '2026-11-02',
            'clock_in' => '07:45:00',
            'clock_out' => '16:10:00',
            'status' => 'Hadir',
            'photo_path' => $masuk,
            'clock_out_photo_path' => $pulang,
        ]);

        Logbook::create([
            'user_id' => $magang->id,
            'date' => '2026-11-02',
            'topic' => 'Topik',
            'description' => 'Deskripsi',
            'status' => 'Disetujui',
        ]);

        LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => 'Izin',
            'start_date' => '2026-11-03',
            'end_date' => '2026-11-03',
            'reason' => 'Uji',
            'document_path' => $dokumen,
            'status' => 'Disetujui',
        ]);

        Feedback::create([
            'user_id' => $magang->id,
            'category' => 'Saran',
            'subject' => 'Saran uji',
            'message' => 'Isi saran untuk pengujian.',
            'status' => 'Baru',
        ]);
    }

    private function konfirmasi(array $override = []): array
    {
        return array_merge([
            'password' => 'rahasia123',
            'confirmation' => 'HAPUS',
        ], $override);
    }

    /*
    |--------------------------------------------------------------------------
    | HAK AKSES & KONFIRMASI
    |--------------------------------------------------------------------------
    */

    public function test_admin_dapat_membuka_halaman_reset_dengan_jumlah_data(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reset-data.view'))
            ->assertOk()
            ->assertSee('1 akun peserta')
            ->assertSee('1 data absensi');
    }

    public function test_hr_tidak_dapat_membuka_atau_menjalankan_reset(): void
    {
        $hr = User::factory()->create(['role' => 'hr', 'password' => bcrypt('rahasia123')]);

        $this->actingAs($hr)->get(route('admin.reset-data.view'))->assertForbidden();
        $this->actingAs($hr)->delete(route('admin.reset-data.users'), $this->konfirmasi())->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $this->alumni->id]);
    }

    public function test_password_salah_membatalkan_reset(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.reset-data.attendance'), $this->konfirmasi(['password' => 'salah']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('attendances', 2);
    }

    public function test_kata_konfirmasi_harus_persis_hapus(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.reset-data.logbooks'), $this->konfirmasi(['confirmation' => 'hapus']))
            ->assertSessionHasErrors('confirmation');

        $this->assertDatabaseCount('logbooks', 2);
    }

    /*
    |--------------------------------------------------------------------------
    | EKSEKUSI RESET
    |--------------------------------------------------------------------------
    */

    public function test_reset_absensi_hanya_peserta_selesai_dan_foto_ikut_terhapus(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.reset-data.attendance'), $this->konfirmasi())
            ->assertRedirect(route('admin.reset-data.view'))
            ->assertSessionHas('success');

        $this->assertSame(0, Attendance::where('user_id', $this->alumni->id)->count());
        $this->assertSame(1, Attendance::where('user_id', $this->aktif->id)->count());

        Storage::disk('local')->assertMissing("attendance/selfies/masuk-{$this->alumni->id}.jpg");
        Storage::disk('local')->assertMissing("attendance/selfies/pulang-{$this->alumni->id}.jpg");
        Storage::disk('local')->assertExists("attendance/selfies/masuk-{$this->aktif->id}.jpg");
    }

    public function test_reset_logbook_hanya_peserta_selesai(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.reset-data.logbooks'), $this->konfirmasi())
            ->assertSessionHas('success');

        $this->assertSame(0, Logbook::where('user_id', $this->alumni->id)->count());
        $this->assertSame(1, Logbook::where('user_id', $this->aktif->id)->count());
    }

    public function test_reset_akun_menghapus_peserta_selesai_beserta_data_dan_file(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.reset-data.users'), $this->konfirmasi())
            ->assertSessionHas('success');

        // Peserta selesai beserta seluruh relasinya hilang.
        $this->assertDatabaseMissing('users', ['id' => $this->alumni->id]);
        $this->assertSame(0, Attendance::where('user_id', $this->alumni->id)->count());
        $this->assertSame(0, Logbook::where('user_id', $this->alumni->id)->count());
        $this->assertSame(0, LeaveRequest::where('user_id', $this->alumni->id)->count());
        $this->assertSame(0, Feedback::where('user_id', $this->alumni->id)->count());

        Storage::disk('local')->assertMissing("surat-izin/izin-{$this->alumni->id}.pdf");
        Storage::disk('public')->assertMissing('profiles/alumni.jpg');

        // Peserta aktif, mentor, dan admin tetap ada.
        $this->assertDatabaseHas('users', ['id' => $this->aktif->id]);
        $this->assertDatabaseHas('users', ['id' => $this->mentor->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $this->assertSame(1, Attendance::where('user_id', $this->aktif->id)->count());
        Storage::disk('local')->assertExists("surat-izin/izin-{$this->aktif->id}.pdf");
    }
}
