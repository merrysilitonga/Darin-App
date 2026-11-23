<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Bantuan lokasi: akurasi GPS di halaman Mulai Absen dan
 * alat pengisian koordinat di halaman Admin > Lokasi Kerja.
 */
class BantuanLokasiTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aturJadwalKerja();
        $this->setWaktuWib('2026-11-16 09:00:00');
    }

    public function test_halaman_absen_menampilkan_akurasi_gps_dan_peringatan(): void
    {
        $magang = $this->buatMagang($this->buatLokasi());

        $this->actingAs($magang)
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Akurasi GPS')
            ->assertSee('id="akurasiGPS"', false)
            // Peringatan disiapkan tetapi tersembunyi sampai akurasi terbaca buruk.
            ->assertSee('id="peringatanAkurasi" class="mt-3 hidden', false)
            ->assertSee('Lokasi kurang akurat')
            ->assertSee('BATAS_AKURASI_METER = 100', false)
            ->assertSee('tampilkanAkurasi(position.coords.accuracy)', false);
    }

    public function test_kirim_absen_memakai_posisi_gps_terakhir_agar_tidak_timeout(): void
    {
        $magang = $this->buatMagang($this->buatLokasi());

        // Posisi dari pemantauan GPS dipakai ulang saat Kirim (≤ 30 detik),
        // dan menjadi cadangan (≤ 5 menit) bila permintaan GPS baru timeout.
        $this->actingAs($magang)
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('const UMUR_POSISI_SEGAR_MS = 30000;', false)
            ->assertSee('const UMUR_POSISI_CADANGAN_MS = 300000;', false)
            ->assertSee('if (error.code === 3 && posisiCadangan)', false)
            ->assertSee('maximumAge: UMUR_POSISI_SEGAR_MS', false);
    }

    public function test_kartu_verifikasi_lokasi_menampilkan_pusat_magang_dan_radius(): void
    {
        $magang = $this->buatMagang($this->buatLokasi(['name' => 'Sekretariat Jenderal KESDM', 'radius_meters' => 600]));

        $this->actingAs($magang)
            ->get(route('absen'))
            ->assertOk()
            ->assertSeeInOrder(['Ambil Selfie', 'Verifikasi Lokasi', 'Pusat Magang', 'Sekretariat Jenderal KESDM'])
            ->assertSee('(-6.2', false)
            ->assertSee('id="statusRadius"', false)
            ->assertSee('Jarak ke Pusat')
            ->assertSee('Batas Diizinkan')
            ->assertSee('600 meter')
            ->assertSee("'Sesuai Radius'", false)
            ->assertSee("'Di Luar Radius'", false);
    }

    public function test_form_izin_di_atas_dan_status_gps_tidak_dobel(): void
    {
        $magang = $this->buatMagang($this->buatLokasi());

        $html = $this->actingAs($magang)
            ->get(route('absen'))
            ->assertOk()
            // Form izin tampil sebelum area kamera agar tidak perlu scroll.
            ->assertSeeInOrder(['id="form-izin"', 'id="section-absen"'], false)
            ->getContent();

        // Label status GPS di pojok kamera sudah dihapus; status hanya di badge kartu lokasi.
        $this->assertStringNotContainsString('id="gpsText"', $html);
        $this->assertSame(1, substr_count($html, 'id="statusRadius"'));
    }

    public function test_akurasi_tidak_tampil_jika_belum_ada_lokasi_kantor(): void
    {
        $magang = $this->buatMagang();

        $this->actingAs($magang)
            ->get(route('absen'))
            ->assertOk()
            ->assertSee('Lokasi Kantor Belum Tersedia')
            ->assertDontSee('id="akurasiGPS"', false);
    }

    public function test_form_lokasi_admin_punya_alat_isi_koordinat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lokasi = $this->buatLokasi();

        $respons = $this->actingAs($admin)
            ->get(route('admin.locations'))
            ->assertOk()
            ->assertSee('Tempel Koordinat dari Google Maps')
            ->assertSee('Gunakan Lokasi Saya Sekarang')
            // Form tambah lokasi.
            ->assertSee('data-lat="latitude" data-lng="longitude"', false)
            // Form edit lokasi yang sudah ada.
            ->assertSee('data-lat="edit-latitude-' . $lokasi->id . '" data-lng="edit-longitude-' . $lokasi->id . '"', false);

        // Script alat hanya dimuat sekali walau dipakai di beberapa form.
        $this->assertSame(1, substr_count($respons->getContent(), 'function uraikanKoordinat('));
    }

    public function test_koordinat_dari_alat_tetap_divalidasi_server(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Latitude dan longitude tertukar (106 di latitude) tetap ditolak server.
        $this->actingAs($admin)
            ->from(route('admin.locations'))
            ->post(route('admin.locations.store'), [
                'name' => 'Kantor Tertukar',
                'latitude' => '106.8295600',
                'longitude' => '-6.1823400',
                'radius_meters' => 100,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.locations'))
            ->assertSessionHasErrors('latitude');

        $this->actingAs($admin)
            ->post(route('admin.locations.store'), [
                'name' => 'Kantor Benar',
                'latitude' => '-6.1823400',
                'longitude' => '106.8295600',
                'radius_meters' => 100,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('locations', ['name' => 'Kantor Benar']);
    }
}
