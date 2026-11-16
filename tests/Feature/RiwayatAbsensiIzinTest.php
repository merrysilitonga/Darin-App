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
 * Riwayat Absensi & Izin digabung dalam satu halaman peserta.
 */
class RiwayatAbsensiIzinTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $magang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');
        $this->magang = $this->buatMagang();
    }

    private function izin(string $mulai, string $selesai, string $jenis = 'Izin', string $status = 'Disetujui', array $atribut = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge([
            'user_id' => $this->magang->id,
            'request_type' => $jenis,
            'start_date' => $mulai,
            'end_date' => $selesai,
            'reason' => "Alasan {$jenis} {$mulai}",
            'document_path' => 'surat-izin/uji.pdf',
            'status' => $status,
        ], $atribut));
    }

    private function riwayat(array $query = [])
    {
        return $this->actingAs($this->magang)->get(route('riwayat.absen', $query));
    }

    public function test_absensi_dan_izin_tampil_di_satu_halaman(): void
    {
        Attendance::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-10',
            'clock_in' => '07:50:00',
            'status' => 'Hadir',
            'work_location' => 'WFO',
        ]);

        $this->izin('2026-11-12', '2026-11-12', 'Sakit');
        $this->izin('2026-11-18', '2026-11-18', 'Izin', 'Ditolak', ['review_notes' => 'Surat kurang jelas']);

        $response = $this->riwayat(['bulan' => '2026-11']);

        $response->assertOk()
            ->assertSee('Riwayat Absensi &amp; Izin', false)
            ->assertSee('Sakit · Disetujui')
            ->assertSee('Izin · Ditolak')
            ->assertSee('Surat kurang jelas')
            ->assertSee('id="riwayat-izin"', false);

        $this->assertSame(1, $response->viewData('ringkasan')['hadir']);
        $this->assertSame(1, $response->viewData('ringkasan')['sakit']);
        $this->assertCount(3, $response->viewData('riwayat'));
    }

    public function test_hari_izin_melewati_akhir_pekan_dan_tidak_menggandakan_hari_absen(): void
    {
        Attendance::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-13',
            'clock_in' => '07:50:00',
            'status' => 'Hadir',
        ]);

        // Kamis 12 s.d. Senin 16 Nov: 12, (13 sudah absen), 16 → 2 baris izin.
        $this->izin('2026-11-12', '2026-11-16');

        $riwayat = collect($this->riwayat(['bulan' => '2026-11'])->viewData('riwayat'));

        $this->assertSame(
            ['2026-11-16', '2026-11-13', '2026-11-12'],
            $riwayat->pluck('tanggal')->all()
        );
        $this->assertSame(['izin', 'absen', 'izin'], $riwayat->pluck('jenis')->all());
    }

    public function test_izin_bulan_lain_tidak_tampil(): void
    {
        $this->izin('2026-12-03', '2026-12-03', 'Izin', 'Menunggu')
            ->forceFill(['created_at' => '2026-10-20 10:00:00'])
            ->saveQuietly();

        $response = $this->riwayat(['bulan' => '2026-11']);

        $this->assertCount(0, $response->viewData('leaveRequests'));
        $response->assertSee('Tidak ada pengajuan izin/sakit pada periode ini');
    }

    public function test_absensi_tanggal_terakhir_bulan_ikut_tampil(): void
    {
        Attendance::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-30',
            'clock_in' => '07:50:00',
            'status' => 'Hadir',
        ]);

        $this->assertCount(1, $this->riwayat(['bulan' => '2026-11'])->viewData('attendances'));
    }

    public function test_tautan_riwayat_izin_lama_diarahkan_ke_halaman_gabungan(): void
    {
        $this->actingAs($this->magang)
            ->get(route('izin.history', ['bulan' => '2026-11']))
            ->assertRedirect(route('riwayat.absen', ['bulan' => '2026-11']) . '#riwayat-izin');
    }

    public function test_setelah_mengajukan_izin_diarahkan_ke_riwayat_gabungan(): void
    {
        $this->actingAs($this->magang)
            ->post(route('izin.store'), [
                'request_type' => 'Sakit',
                'start_date' => '2026-12-02',
                'end_date' => '2026-12-02',
                'reason' => 'Demam tinggi',
                'document' => UploadedFile::fake()->create('surat.pdf', 50, 'application/pdf'),
            ])
            ->assertRedirect(route('riwayat.absen', ['bulan' => '2026-12']) . '#riwayat-izin')
            ->assertSessionHas('success');
    }

    public function test_menu_riwayat_izin_terpisah_sudah_tidak_ada(): void
    {
        $this->riwayat()
            ->assertSee('Riwayat Absensi &amp; Izin', false)
            ->assertDontSee('Riwayat Izin / Sakit');
    }

    public function test_pdf_dan_csv_memuat_hari_sakit(): void
    {
        $this->izin('2026-11-03', '2026-11-04', 'Sakit');

        // PDF berhasil diunduh.
        $pdf = $this->actingAs($this->magang)->get(route('riwayat.export-pdf', ['bulan' => '2026-11']));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('Content-Type'));

        // Isi template PDF memuat hari sakit, logo, dan kolom tanda tangan.
        $data = $this->riwayat(['bulan' => '2026-11'])->viewData('riwayat');
        $html = view('attendance.export-pdf', [
            'user' => $this->magang->fresh(['division', 'location', 'mentor']),
            'periode' => \Carbon\Carbon::create(2026, 11, 1, 0, 0, 0, 'Asia/Jakarta'),
            'riwayat' => array_reverse($data),
            'ringkasan' => ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 2, 'menunggu' => 0],
        ])->render();

        $this->assertStringContainsString('Laporan Absensi Peserta Magang', $html);
        $this->assertStringContainsString('03/11/2026', $html);
        $this->assertStringContainsString('Sakit (Disetujui)', $html);
        $this->assertStringContainsString('Disetujui Oleh', $html);

        // CSV juga memuat hari sakit.
        $csv = $this->actingAs($this->magang)->get(route('riwayat.export-csv', ['bulan' => '2026-11']));
        $csv->assertOk();
        $this->assertStringContainsString('Sakit (Disetujui)', $csv->streamedContent());
    }
}
