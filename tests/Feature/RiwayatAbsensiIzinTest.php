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
        $this->assertCount(3, collect($response->viewData('riwayat'))->whereIn('jenis', ['absen', 'izin']));
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

        $riwayat = collect($this->riwayat(['bulan' => '2026-11'])->viewData('riwayat'))
            ->whereIn('jenis', ['absen', 'izin'])
            ->values();

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

    public function test_setelah_mengajukan_izin_kembali_ke_halaman_mulai_absen(): void
    {
        $this->actingAs($this->magang)
            ->post(route('izin.store'), [
                'request_type' => 'Sakit',
                'start_date' => '2026-12-02',
                'end_date' => '2026-12-02',
                'reason' => 'Demam tinggi',
                'document' => UploadedFile::fake()->create('surat.pdf', 50, 'application/pdf'),
            ])
            ->assertRedirect(route('absen'))
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

    public function test_hari_kerja_kosong_tampil_sebagai_alfa_dan_libur(): void
    {
        \App\Models\Holiday::create([
            'date' => '2026-11-06',
            'name' => 'Libur Uji',
            'type' => 'Nasional',
            'is_active' => true,
        ]);

        Attendance::create([
            'user_id' => $this->magang->id,
            'date' => '2026-11-02',
            'clock_in' => '07:50:00',
            'status' => 'Hadir',
        ]);

        $this->izin('2026-11-03', '2026-11-03', 'Sakit');
        $this->izin('2026-11-04', '2026-11-04', 'Izin', 'Ditolak');

        $response = $this->riwayat(['bulan' => '2026-11']);
        $riwayat = collect($response->viewData('riwayat'))->keyBy('tanggal');

        // 6 Nov libur, 5 & 9–13 Nov alfa, 16 Nov (hari ini) belum dihitung.
        $this->assertSame('libur', $riwayat['2026-11-06']['jenis']);
        $this->assertSame('alfa', $riwayat['2026-11-05']['jenis']);
        $this->assertSame('alfa', $riwayat['2026-11-13']['jenis']);
        $this->assertArrayNotHasKey('2026-11-16', $riwayat->all());
        $this->assertArrayNotHasKey('2026-11-14', $riwayat->all()); // Sabtu

        // Alfa = 6 hari kosong + 1 hari izin ditolak.
        $this->assertSame(7, $response->viewData('ringkasan')['alfa']);
        $response->assertSee('Libur Uji');
    }

    public function test_filter_bulan_dibatasi_ke_periode_magang(): void
    {
        // Periode magang 1 Nov – 31 Des 2026.
        $sebelum = $this->riwayat(['bulan' => '2026-08']);
        $this->assertSame('2026-11', $sebelum->viewData('periode')->format('Y-m'));
        $sebelum->assertSee('min="2026-11"', false)->assertSee('max="2026-12"', false);

        $sesudah = $this->riwayat(['bulan' => '2027-03']);
        $this->assertSame('2026-12', $sesudah->viewData('periode')->format('Y-m'));
    }

    public function test_laporan_akhir_magang_dari_halaman_riwayat_bukan_sidebar(): void
    {
        $this->riwayat()
            ->assertSee('Laporan Akhir Magang')
            ->assertSee(route('rekap.saya'), false)
            ->assertDontSee('Rekap Magang Saya');

        $this->actingAs($this->magang)
            ->get(route('rekap.saya'))
            ->assertOk();
    }

    public function test_form_pengajuan_izin_berada_di_halaman_mulai_absen(): void
    {
        // Halaman pengajuan lama diarahkan ke form di halaman Mulai Absen.
        $this->actingAs($this->magang)
            ->get(route('izin.create'))
            ->assertRedirect(route('absen', ['ajukan' => 1]) . '#form-izin');

        // Form tidak lagi berada di halaman riwayat.
        $this->riwayat()
            ->assertDontSee('id="form-izin"', false)
            ->assertDontSee('action="' . route('izin.store') . '"', false);

        // Form terbuka otomatis dan tanggal dibatasi ke periode magang.
        $this->actingAs($this->magang)
            ->get(route('absen', ['ajukan' => 1]))
            ->assertOk()
            ->assertSee('id="form-izin"', false)
            ->assertSee('action="' . route('izin.store') . '"', false)
            ->assertSee('min="2026-11-01"', false)
            ->assertSee('max="2026-12-31"', false);

        // Menu terpisah di sidebar sudah tidak ada.
        $this->actingAs($this->magang)
            ->get(route('dashboard.magang'))
            ->assertDontSee('Pengajuan Izin / Sakit');
    }

    public function test_error_validasi_kembali_ke_form_di_halaman_mulai_absen(): void
    {
        $this->actingAs($this->magang)
            ->from(route('absen'))
            ->post(route('izin.store'), [
                '_form' => 'izin',
                'request_type' => 'Sakit',
                'start_date' => '2026-11-20',
                'end_date' => '2026-11-20',
                'reason' => '',
            ])
            ->assertRedirect(route('absen'))
            ->assertSessionHasErrors(['reason', 'document']);
    }

    public function test_error_form_izin_tidak_tercampur_dengan_error_absensi(): void
    {
        // Bentuk error di session sama seperti yang disimpan Laravel saat
        // config session.serialization = 'json' (dibaca ulang lewat marshalErrorBag).
        $errorBag = fn (string $pesan) => [
            'default' => [
                'format' => ':message',
                'messages' => ['field' => [$pesan]],
            ],
        ];

        // Error dari form izin tampil di dalam form izin, bukan di kotak error absensi.
        $this->actingAs($this->magang)
            ->withSession([
                'errors' => $errorBag('Alasan pengajuan wajib diisi.'),
                '_old_input' => ['_form' => 'izin'],
            ])
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Pengajuan belum terkirim')
            ->assertSee('Alasan pengajuan wajib diisi.')
            ->assertDontSee('Terjadi kesalahan:');

        // Error absensi biasa tetap tampil di kotak error absensi.
        $this->actingAs($this->magang)
            ->withSession([
                'errors' => $errorBag('Foto selfie wajib diambil.'),
                '_old_input' => [],
            ])
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Terjadi kesalahan:')
            ->assertSee('Foto selfie wajib diambil.')
            ->assertDontSee('Pengajuan belum terkirim');
    }

    public function test_peserta_yang_magangnya_selesai_tidak_melihat_form(): void
    {
        $alumni = $this->buatMagang(null, ['start_date' => '2026-09-01', 'end_date' => '2026-11-15']);

        $this->actingAs($alumni)
            ->get(route('absen', ['ajukan' => 1]))
            ->assertOk()
            ->assertSee('tidak dapat mengajukan izin baru')
            ->assertDontSee('action="' . route('izin.store') . '"', false);
    }
}
