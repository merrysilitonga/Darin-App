<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\InternRecapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Dinas Luar menunggu persetujuan Mentor:
 * disetujui → hadir; ditolak → Alfa. Absen pulang tetap bisa dilakukan.
 */
class PersetujuanDinasLuarTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $mentor;
    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();
        // Senin, 16 November 2026.
        $this->setWaktuWib('2026-11-16 07:45:00');

        $this->mentor = User::factory()->create(['role' => 'mentor']);
        $this->magang = $this->buatMagang($this->buatLokasi(), ['mentor_id' => $this->mentor->id]);
    }

    private function absenDinas(): Attendance
    {
        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen($this->koordinatLuarRadius() + [
                'work_location' => 'Dinas Luar',
                'tujuan_dinas' => 'Rapat di Dinas ESDM Jawa Barat',
            ]))
            ->assertSessionHas('success');

        return Attendance::firstOrFail();
    }

    public function test_absen_dinas_berstatus_menunggu_dan_tampil_di_mentor(): void
    {
        $absen = $this->absenDinas();

        $this->assertSame('Menunggu', $absen->dinas_status);

        $this->actingAs($this->magang)
            ->get(route('absen'))
            ->assertSee('Menunggu persetujuan Mentor');

        $this->actingAs($this->mentor)
            ->get(route('mentor.leave-requests'))
            ->assertOk()
            ->assertSee('Persetujuan Dinas Luar')
            ->assertSee('Rapat di Dinas ESDM Jawa Barat')
            ->assertSee(route('mentor.dinas.approve', $absen->id), false);
    }

    public function test_absen_kantor_biasa_tidak_perlu_persetujuan(): void
    {
        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.store'), $this->dataAbsen())
            ->assertSessionHas('success');

        $this->assertNull(Attendance::firstOrFail()->dinas_status);
    }

    public function test_absen_pulang_tetap_bisa_sebelum_disetujui(): void
    {
        $this->absenDinas();
        $this->setWaktuWib('2026-11-16 16:30:00');

        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('absen.checkout'), $this->dataAbsen($this->koordinatLuarRadius()))
            ->assertSessionHas('success');

        $absen = Attendance::firstOrFail();
        $this->assertNotNull($absen->clock_out);
        $this->assertSame('Menunggu', $absen->dinas_status);
    }

    public function test_mentor_menyetujui_dinas(): void
    {
        $absen = $this->absenDinas();

        $this->actingAs($this->mentor)
            ->from(route('mentor.leave-requests'))
            ->post(route('mentor.dinas.approve', $absen->id))
            ->assertSessionHas('success');

        $absen->refresh();
        $this->assertSame('Disetujui', $absen->dinas_status);
        $this->assertSame($this->mentor->id, (int) $absen->dinas_reviewed_by);

        $this->setWaktuWib('2026-11-17 09:00:00');
        $rekap = app(InternRecapService::class)->build($this->magang->fresh());
        $this->assertSame(1, $rekap['summary']['hadir']);
    }

    public function test_mentor_menolak_dinas_wajib_alasan_dan_dihitung_alfa(): void
    {
        $absen = $this->absenDinas();

        $this->actingAs($this->mentor)
            ->from(route('mentor.leave-requests'))
            ->post(route('mentor.dinas.reject', $absen->id), ['dinas_review_notes' => ''])
            ->assertSessionHasErrors('dinas_review_notes');

        $this->assertSame('Menunggu', $absen->fresh()->dinas_status);

        $this->actingAs($this->mentor)
            ->from(route('mentor.leave-requests'))
            ->post(route('mentor.dinas.reject', $absen->id), ['dinas_review_notes' => 'Tidak ada surat tugas'])
            ->assertSessionHas('success');

        $this->assertSame('Ditolak', $absen->fresh()->dinas_status);

        $this->setWaktuWib('2026-11-17 09:00:00');
        $rekap = app(InternRecapService::class)->build($this->magang->fresh());
        $this->assertSame(0, $rekap['summary']['hadir']);
        $this->assertGreaterThanOrEqual(1, $rekap['summary']['alfa']);

        $hari = collect($rekap['days'])->first(fn ($d) => $d['date']->toDateString() === '2026-11-16');
        $this->assertSame('Alfa', $hari['status']);
        $this->assertStringContainsString('Tidak ada surat tugas', $hari['note']);
    }

    public function test_mentor_lain_tidak_bisa_memproses(): void
    {
        $absen = $this->absenDinas();
        $mentorLain = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($mentorLain)
            ->post(route('mentor.dinas.approve', $absen->id))
            ->assertNotFound();

        $this->actingAs($mentorLain)
            ->get(route('mentor.dinas.photo', $absen->id))
            ->assertNotFound();

        $this->assertSame('Menunggu', $absen->fresh()->dinas_status);
    }

    public function test_mentor_bisa_melihat_selfie_dinas(): void
    {
        $absen = $this->absenDinas();

        $this->actingAs($this->mentor)
            ->get(route('mentor.dinas.photo', $absen->id))
            ->assertOk();
    }

    public function test_tidak_bisa_diproses_dua_kali(): void
    {
        $absen = $this->absenDinas();

        $this->actingAs($this->mentor)->post(route('mentor.dinas.approve', $absen->id));

        $this->actingAs($this->mentor)
            ->from(route('mentor.leave-requests'))
            ->post(route('mentor.dinas.reject', $absen->id), ['dinas_review_notes' => 'x'])
            ->assertSessionHas('error');

        $this->assertSame('Disetujui', $absen->fresh()->dinas_status);
    }

    public function test_badge_sidebar_mentor_menghitung_dinas(): void
    {
        $this->absenDinas();

        $this->actingAs($this->mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('izin, koreksi absen pulang & Dinas Luar', false);
    }

    /** Izin 16–17 Nov disetujui; tanggal 17 ternyata absen Dinas Luar. */
    private function izinLaluDinasHariKedua(): Attendance
    {
        LeaveRequest::create([
            'user_id' => $this->magang->id,
            'request_type' => 'Sakit',
            'start_date' => '2026-11-16',
            'end_date' => '2026-11-17',
            'reason' => 'Demam',
            'document_path' => 'surat-izin/uji.pdf',
            'status' => 'Disetujui',
        ]);

        $this->setWaktuWib('2026-11-17 07:45:00');

        return $this->absenDinas();
    }

    public function test_izin_dan_dinas_di_rentang_sama_singkron_di_riwayat_dan_rekap_mentor(): void
    {
        $this->izinLaluDinasHariKedua();
        $this->setWaktuWib('2026-11-17 16:00:00');

        $respon = $this->actingAs($this->magang)->get(route('riwayat.absen', ['bulan' => '2026-11']));
        $respon->assertOk()
            ->assertSee('Tetap absen (Dinas Luar/hadir) pada')
            ->assertSee('17 Nov 2026');
        $ringkasan = $respon->viewData('ringkasan');
        $this->assertSame(1, $ringkasan['sakit']);
        $this->assertSame(1, $ringkasan['hadir']);

        $riwayat = collect($respon->viewData('riwayat'));
        $this->assertSame('izin', $riwayat->firstWhere('tanggal', '2026-11-16')['jenis']);
        $this->assertSame('absen', $riwayat->firstWhere('tanggal', '2026-11-17')['jenis']);

        $rekapMentor = $this->actingAs($this->mentor)->get(route('mentor.attendance', ['month' => '2026-11']));
        $rekapMentor->assertOk()->assertSee('1 Dinas Luar');
        $baris = collect($rekapMentor->viewData('attendanceSummaries'))->first();
        $this->assertSame(1, $baris['hadir']);
        $this->assertSame(1, $baris['sakit']);
    }

    public function test_dinas_ditolak_di_hari_izin_kembali_dihitung_izin(): void
    {
        $absen = $this->izinLaluDinasHariKedua();

        $this->actingAs($this->mentor)
            ->from(route('mentor.leave-requests'))
            ->post(route('mentor.dinas.reject', $absen->id), ['dinas_review_notes' => 'Tidak ada surat tugas']);

        $this->setWaktuWib('2026-11-18 09:00:00');

        $ringkasan = $this->actingAs($this->magang)
            ->get(route('riwayat.absen', ['bulan' => '2026-11']))
            ->viewData('ringkasan');
        $this->assertSame(2, $ringkasan['sakit']);
        $this->assertSame(0, $ringkasan['hadir']);

        $baris = collect($this->actingAs($this->mentor)
            ->get(route('mentor.attendance', ['month' => '2026-11']))
            ->viewData('attendanceSummaries'))->first();
        $this->assertSame(0, $baris['hadir']);
        $this->assertSame(2, $baris['sakit']);

        $rekap = app(InternRecapService::class)->build($this->magang->fresh());
        $this->assertSame(2, $rekap['summary']['sakit']);
    }
}
