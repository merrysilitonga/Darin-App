<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Fitur saran & masukan: peserta mengirim, Admin/HR menanggapi.
 */
class SaranMasukanTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');
    }

    private function buatAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function dataSaran(array $override = []): array
    {
        return array_merge([
            'category' => 'Kendala Aplikasi',
            'subject' => 'GPS sering tidak terbaca',
            'message' => 'Saat absen pagi, lokasi GPS butuh waktu lama untuk terbaca.',
        ], $override);
    }

    private function buatSaran(User $user, array $override = []): Feedback
    {
        return Feedback::create(array_merge($this->dataSaran(), [
            'user_id' => $user->id,
            'status' => Feedback::STATUS_NEW,
        ], $override));
    }

    /*
    |--------------------------------------------------------------------------
    | PESERTA MAGANG
    |--------------------------------------------------------------------------
    */

    public function test_magang_dapat_membuka_halaman_saran(): void
    {
        $this->actingAs($this->buatMagang())
            ->get(route('saran'))
            ->assertOk()
            ->assertSee('Kirim Saran Baru');
    }

    public function test_magang_dapat_mengirim_saran(): void
    {
        $magang = $this->buatMagang();

        $this->actingAs($magang)
            ->post(route('saran.store'), $this->dataSaran())
            ->assertRedirect(route('saran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $magang->id,
            'subject' => 'GPS sering tidak terbaca',
            'status' => Feedback::STATUS_NEW,
        ]);
    }

    public function test_saran_dengan_isi_terlalu_pendek_ditolak(): void
    {
        $this->actingAs($this->buatMagang())
            ->post(route('saran.store'), $this->dataSaran(['message' => 'pendek']))
            ->assertSessionHasErrors('message');

        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_kategori_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->buatMagang())
            ->post(route('saran.store'), $this->dataSaran(['category' => 'Gosip']))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_kiriman_melebihi_batas_harian_ditolak(): void
    {
        $magang = $this->buatMagang();

        for ($i = 0; $i < Feedback::DAILY_LIMIT; $i++) {
            $this->buatSaran($magang);
        }

        $this->actingAs($magang)
            ->post(route('saran.store'), $this->dataSaran())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('feedbacks', Feedback::DAILY_LIMIT);
    }

    public function test_magang_hanya_melihat_saran_miliknya_dan_tanggapan_admin(): void
    {
        $magang = $this->buatMagang();
        $lain = $this->buatMagang();

        $this->buatSaran($magang, [
            'subject' => 'Saran Milik Saya',
            'admin_reply' => 'Terima kasih, sedang kami perbaiki.',
            'status' => Feedback::STATUS_PROCESS,
        ]);
        $this->buatSaran($lain, ['subject' => 'Saran Peserta Lain']);

        $this->actingAs($magang)
            ->get(route('saran'))
            ->assertOk()
            ->assertSee('Saran Milik Saya')
            ->assertSee('Terima kasih, sedang kami perbaiki.')
            ->assertDontSee('Saran Peserta Lain');
    }

    public function test_magang_yang_periodenya_berakhir_tidak_bisa_mengirim_saran(): void
    {
        $magang = $this->buatMagang(null, [
            'start_date' => '2026-09-01',
            'end_date' => '2026-11-15',
        ]);

        $this->actingAs($magang)
            ->post(route('saran.store'), $this->dataSaran());

        $this->assertDatabaseCount('feedbacks', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN / HR
    |--------------------------------------------------------------------------
    */

    public function test_admin_dapat_melihat_daftar_saran(): void
    {
        $this->buatSaran($this->buatMagang(null, ['name' => 'Budi Peserta']), ['subject' => 'Tombol absen lambat']);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.feedbacks'))
            ->assertOk()
            ->assertSee('Tombol absen lambat')
            ->assertSee('Budi Peserta');
    }

    public function test_admin_dapat_memfilter_saran_berdasarkan_status(): void
    {
        $magang = $this->buatMagang();
        $this->buatSaran($magang, ['subject' => 'Saran Baru Satu']);
        $this->buatSaran($magang, ['subject' => 'Saran Sudah Selesai', 'status' => Feedback::STATUS_DONE]);

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.feedbacks', ['status' => Feedback::STATUS_DONE]))
            ->assertOk()
            ->assertSee('Saran Sudah Selesai')
            ->assertDontSee('Saran Baru Satu');
    }

    public function test_admin_dapat_menanggapi_dan_mengubah_status(): void
    {
        $admin = $this->buatAdmin();
        $saran = $this->buatSaran($this->buatMagang());

        $this->actingAs($admin)
            ->put(route('admin.feedbacks.update', $saran), [
                'status' => Feedback::STATUS_DONE,
                'admin_reply' => 'Sudah diperbaiki pada versi terbaru.',
            ])
            ->assertSessionHas('success');

        $saran->refresh();

        $this->assertSame(Feedback::STATUS_DONE, $saran->status);
        $this->assertSame('Sudah diperbaiki pada versi terbaru.', $saran->admin_reply);
        $this->assertSame($admin->id, $saran->replied_by);
        $this->assertNotNull($saran->replied_at);
    }

    public function test_magang_tidak_dapat_membuka_atau_mengubah_saran_di_admin(): void
    {
        $magang = $this->buatMagang();
        $saran = $this->buatSaran($magang);

        $this->actingAs($magang)
            ->get(route('admin.feedbacks'))
            ->assertForbidden();

        $this->actingAs($magang)
            ->put(route('admin.feedbacks.update', $saran), [
                'status' => Feedback::STATUS_DONE,
                'admin_reply' => 'Mencoba mengubah sendiri',
            ])
            ->assertForbidden();

        $saran->refresh();

        $this->assertSame(Feedback::STATUS_NEW, $saran->status);
        $this->assertNull($saran->admin_reply);
    }
}
