<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use App\Services\MentorPendingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Koreksi absen pulang: peserta lupa absen pulang → ajukan jam pulang → mentor setujui/tolak.
 */
class KoreksiAbsenPulangTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $mentor;
    private User $magang;
    private Attendance $absenLupaPulang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        // Senin, 16 November 2026.
        $this->setWaktuWib('2026-11-16 09:00:00');

        $this->mentor = User::factory()->create(['role' => 'mentor']);
        $this->magang = $this->buatMagang($this->buatLokasi(), ['mentor_id' => $this->mentor->id]);

        // Jumat lalu: absen masuk, lupa absen pulang.
        $this->absenLupaPulang = Attendance::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-13',
            'clock_in' => '07:50:00',
            'status' => 'Hadir',
        ]);
    }

    private function ajukan(array $data = [], ?Attendance $absen = null)
    {
        return $this->actingAs($this->magang)
            ->from(route('riwayat.absen'))
            ->post(route('riwayat.koreksi-pulang', ($absen ?? $this->absenLupaPulang)->id), array_merge([
                'clock_out' => '16:30',
                'reason' => 'Lupa absen pulang karena rapat sampai sore.',
            ], $data));
    }

    public function test_riwayat_menampilkan_tombol_koreksi_untuk_hari_lupa_pulang(): void
    {
        $this->actingAs($this->magang)
            ->get(route('riwayat.absen', ['bulan' => '2026-11']))
            ->assertOk()
            ->assertSee('Tidak absen pulang')
            ->assertSee('Ajukan koreksi jam pulang')
            ->assertSee(route('riwayat.koreksi-pulang', $this->absenLupaPulang->id), false);
    }

    public function test_peserta_mengajukan_koreksi_dan_mentor_mendapat_notifikasi(): void
    {
        $this->ajukan()
            ->assertRedirect(route('riwayat.absen'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $this->absenLupaPulang->id,
            'user_id' => $this->magang->id,
            'status' => 'Menunggu',
            'clock_out' => '16:30:00',
        ]);

        // Riwayat menampilkan status menunggu, tombol koreksi hilang.
        $this->actingAs($this->magang)
            ->get(route('riwayat.absen', ['bulan' => '2026-11']))
            ->assertSee('Koreksi 16:30 menunggu Mentor')
            ->assertDontSee('Ajukan koreksi jam pulang');

        $this->assertSame(1, app(MentorPendingService::class)->summary($this->mentor)['koreksi']);

        $this->actingAs($this->mentor)
            ->get(route('mentor.leave-requests'))
            ->assertOk()
            ->assertSee('Koreksi Absen Pulang')
            ->assertSee('Lupa absen pulang karena rapat sampai sore.');
    }

    public function test_validasi_koreksi(): void
    {
        // Jam pulang sebelum jam masuk.
        $this->ajukan(['clock_out' => '07:00'])->assertSessionHasErrors('clock_out');

        // Alasan kosong.
        $this->ajukan(['reason' => ''])->assertSessionHasErrors('reason');

        // Absensi hari ini: pakai absen pulang biasa.
        $hariIni = Attendance::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-16',
            'clock_in' => '07:40:00',
            'status' => 'Hadir',
        ]);
        $this->ajukan([], $hariIni)->assertSessionHas('error');

        // Tidak boleh mengajukan dua kali selama masih menunggu.
        $this->ajukan()->assertSessionHas('success');
        $this->ajukan()->assertSessionHas('error');

        $this->assertSame(1, AttendanceCorrection::count());
    }

    public function test_peserta_tidak_bisa_mengoreksi_absensi_orang_lain(): void
    {
        $lain = $this->buatMagang();
        $absenLain = Attendance::create([
            'user_id' => $lain->id,
            'date' => '2026-11-13',
            'clock_in' => '07:55:00',
            'status' => 'Hadir',
        ]);

        $this->ajukan([], $absenLain)->assertNotFound();
        $this->assertSame(0, AttendanceCorrection::count());
    }

    public function test_mentor_menyetujui_koreksi_jam_pulang_dicatat(): void
    {
        $this->ajukan();
        $koreksi = AttendanceCorrection::firstOrFail();

        $this->actingAs($this->mentor)
            ->post(route('mentor.corrections.approve', $koreksi->id))
            ->assertSessionHas('success');

        $this->assertSame('16:30:00', $this->absenLupaPulang->fresh()->clock_out);
        $this->assertSame('Disetujui', $koreksi->fresh()->status);
        $this->assertSame($this->mentor->id, $koreksi->fresh()->reviewed_by);

        $this->actingAs($this->magang)
            ->get(route('riwayat.absen', ['bulan' => '2026-11']))
            ->assertSee('16:30 WIB')
            ->assertSee('(koreksi disetujui)');
    }

    public function test_mentor_menolak_dengan_alasan_dan_peserta_bisa_mengajukan_ulang(): void
    {
        $this->ajukan();
        $koreksi = AttendanceCorrection::firstOrFail();

        // Alasan penolakan wajib.
        $this->actingAs($this->mentor)
            ->post(route('mentor.corrections.reject', $koreksi->id), ['review_notes' => ''])
            ->assertSessionHasErrors('review_notes');

        $this->actingAs($this->mentor)
            ->post(route('mentor.corrections.reject', $koreksi->id), ['review_notes' => 'Tidak ada bukti rapat.'])
            ->assertSessionHas('success');

        $this->assertSame('Ditolak', $koreksi->fresh()->status);
        $this->assertNull($this->absenLupaPulang->fresh()->clock_out);

        $this->actingAs($this->magang)
            ->get(route('riwayat.absen', ['bulan' => '2026-11']))
            ->assertSee('Koreksi ditolak: Tidak ada bukti rapat.')
            ->assertSee('Ajukan ulang koreksi');

        $this->ajukan(['clock_out' => '17:00'])->assertSessionHas('success');
        $this->assertSame(2, AttendanceCorrection::count());
    }

    public function test_mentor_lain_tidak_bisa_memproses_koreksi(): void
    {
        $this->ajukan();
        $koreksi = AttendanceCorrection::firstOrFail();
        $mentorLain = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($mentorLain)
            ->post(route('mentor.corrections.approve', $koreksi->id))
            ->assertNotFound();

        $this->assertSame('Menunggu', $koreksi->fresh()->status);
    }
}
