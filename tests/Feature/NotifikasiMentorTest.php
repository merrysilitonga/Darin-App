<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use App\Services\MentorPendingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Notifikasi mentor: badge sidebar, lonceng header, dan peringatan item tertunda.
 */
class NotifikasiMentorTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');
    }

    private function buatMentor(): User
    {
        return User::factory()->create(['role' => 'mentor']);
    }

    private function logbookMenunggu(User $magang, string $tanggal, ?string $dikirim = null): Logbook
    {
        return Logbook::create([
            'user_id' => $magang->id,
            'date' => $tanggal,
            'topic' => 'Topik ' . $tanggal,
            'description' => 'Kegiatan harian uji',
            'status' => 'Menunggu',
            'submitted_at' => $dikirim ?? Carbon::now(),
        ]);
    }

    private function izinMenunggu(User $magang, ?Carbon $dibuat = null): LeaveRequest
    {
        $izin = LeaveRequest::create([
            'user_id' => $magang->id,
            'request_type' => 'Izin',
            'start_date' => '2026-11-18',
            'end_date' => '2026-11-18',
            'reason' => 'Urusan keluarga',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Menunggu',
        ]);

        if ($dibuat) {
            $izin->forceFill(['created_at' => $dibuat])->saveQuietly();
        }

        return $izin;
    }

    public function test_ringkasan_hanya_menghitung_mahasiswa_bimbingan_sendiri(): void
    {
        $mentor = $this->buatMentor();
        $magang = $this->buatMagang(null, ['mentor_id' => $mentor->id]);
        $magangLain = $this->buatMagang(null, ['mentor_id' => $this->buatMentor()->id]);

        $this->logbookMenunggu($magang, '2026-11-12');
        $this->logbookMenunggu($magang, '2026-11-13');
        $this->izinMenunggu($magang);

        // Milik mentor lain dan yang sudah disetujui tidak ikut dihitung.
        $this->logbookMenunggu($magangLain, '2026-11-13');
        Logbook::create([
            'user_id' => $magang->id,
            'date' => '2026-11-11',
            'topic' => 'Sudah disetujui',
            'description' => 'Kegiatan harian uji',
            'status' => 'Disetujui',
        ]);

        $ringkasan = app(MentorPendingService::class)->summary($mentor);

        $this->assertSame(2, $ringkasan['logbook']);
        $this->assertSame(1, $ringkasan['leave']);
        $this->assertSame(3, $ringkasan['total']);
        $this->assertSame(0, $ringkasan['overdue']);
    }

    public function test_dashboard_mentor_menampilkan_lonceng_jumlah_tindakan(): void
    {
        $mentor = $this->buatMentor();
        $magang = $this->buatMagang(null, ['mentor_id' => $mentor->id]);

        $this->logbookMenunggu($magang, '2026-11-13');
        $this->izinMenunggu($magang);

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('2 menunggu tindakan')
            ->assertSee('Logbook menunggu validasi', false)
            ->assertSee('Pengajuan menunggu persetujuan', false);
    }

    public function test_item_yang_menunggu_lama_memunculkan_peringatan(): void
    {
        $mentor = $this->buatMentor();
        $magang = $this->buatMagang(null, ['mentor_id' => $mentor->id]);

        $this->izinMenunggu($magang, Carbon::now()->subDays(5));

        $this->assertSame(1, app(MentorPendingService::class)->summary($mentor)['overdue']);

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('hari atau lebih')
            ->assertSee('Menunggu 5 hari');
    }

    public function test_mentor_tanpa_tindakan_menunggu_tidak_melihat_badge(): void
    {
        $mentor = $this->buatMentor();
        $this->buatMagang(null, ['mentor_id' => $mentor->id]);

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada tindakan')
            ->assertDontSee('Logbook menunggu validasi', false)
            ->assertDontSee('hari atau lebih');
    }

    public function test_halaman_magang_tetap_normal_dengan_sidebar_baru(): void
    {
        $this->actingAs($this->buatMagang())
            ->get(route('dashboard.magang'))
            ->assertOk();
    }
}
