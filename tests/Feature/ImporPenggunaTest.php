<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Support\DataAbsensi;
use Tests\TestCase;

/**
 * Impor akun Peserta Magang & Mentor dari CSV oleh Admin/HR.
 */
class ImporPenggunaTest extends TestCase
{
    use RefreshDatabase;
    use DataAbsensi;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@darin.test']);
        Division::create(['name' => 'IT Prasarana']);
    }

    private function unggah(string $csv)
    {
        return $this->actingAs($this->admin)->post(route('admin.users.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('pengguna.csv', $csv),
        ]);
    }

    public function test_halaman_impor_dapat_dibuka_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.import'))
            ->assertOk()
            ->assertSee('Impor Pengguna dari CSV')
            ->assertSee('IT Prasarana');
    }

    public function test_impor_mentor_dan_magang_dalam_satu_file(): void
    {
        $csv = "\xEF\xBB\xBF" . "nama;email;role;nim;universitas;prodi;telepon;nip;jabatan;divisi;email_mentor;tanggal_mulai;tanggal_selesai;password\n"
            . "Sinta Rahmawati;Sinta@Kampus.ac.id;magang;2101;Univ Contoh;SI;0812;;;it prasarana;budi@esdm.go.id;01/11/2026;2026-12-31;\n"
            . "Budi Santoso;budi@esdm.go.id;mentor;;;;0813;1987;Analis;IT Prasarana;;;;rahasia123\n";

        $this->unggah($csv)
            ->assertRedirect(route('admin.users.import'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $mentor = User::where('email', 'budi@esdm.go.id')->firstOrFail();
        $magang = User::where('email', 'sinta@kampus.ac.id')->firstOrFail();

        $this->assertSame('mentor', $mentor->role);
        $this->assertSame('1987', $mentor->nip);
        $this->assertTrue(Hash::check('rahasia123', $mentor->password));

        $this->assertSame('magang', $magang->role);
        $this->assertSame($mentor->id, (int) $magang->mentor_id);
        $this->assertSame('IT Prasarana', $magang->division->name);
        $this->assertSame('2026-11-01', $magang->start_date->toDateString());
        $this->assertSame('2026-12-31', $magang->end_date->toDateString());

        // Password otomatis ditampilkan sekali dan bisa dipakai login.
        $akun = collect(session('import_credentials'))->keyBy('email');
        $this->assertNull($akun['budi@esdm.go.id']['password']);
        $this->assertTrue(Hash::check($akun['sinta@kampus.ac.id']['password'], $magang->password));
    }

    public function test_baris_tidak_valid_membatalkan_seluruh_impor(): void
    {
        $csv = "nama,email,role,divisi,email_mentor,tanggal_mulai,tanggal_selesai\n"
            . "Valid Satu,valid@kampus.ac.id,magang,,,,\n"
            . "Email Terpakai,admin@darin.test,magang,,,,\n"
            . "Role Salah,role@kampus.ac.id,admin,,,,\n"
            . "Divisi Salah,divisi@kampus.ac.id,magang,Divisi Fiktif,,,\n"
            . "Tanggal Salah,tanggal@kampus.ac.id,magang,,,31/02/2026,2026-12-31\n"
            . "Mentor Hilang,mentor@kampus.ac.id,magang,,tidakada@esdm.go.id,,\n"
            . "Duplikat,valid@kampus.ac.id,magang,,,,\n";

        $this->unggah($csv)->assertSessionHasErrorsIn('import');

        $this->assertCount(6, session('errors')->getBag('import')->all());
        $this->assertSame(1, User::count()); // hanya admin
    }

    public function test_header_wajib_harus_ada(): void
    {
        $this->unggah("nama;telepon\nTanpa Email;0812\n")
            ->assertSessionHasErrorsIn('import');

        $this->assertSame(1, User::count());
    }

    public function test_template_dapat_diunduh(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.import.template'));

        $response->assertOk();
        $this->assertStringContainsString('nama;email;role', $response->getContent());
    }

    public function test_magang_tidak_dapat_mengimpor_pengguna(): void
    {
        $this->actingAs($this->buatMagang())
            ->post(route('admin.users.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pengguna.csv', "nama;email;role\nX;x@y.id;magang\n"),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'x@y.id']);
    }

    public function test_kolom_lokasi_membuat_peserta_langsung_bisa_absen(): void
    {
        $lokasi = $this->buatLokasi(['name' => 'Kementerian ESDM']);

        $this->unggah("nama;email;role;lokasi;tanggal_mulai;tanggal_selesai\n"
            . "Sinta;sinta@kampus.ac.id;magang;kementerian esdm;2026-11-01;2026-12-31\n"
            . "Budi;budi@esdm.go.id;mentor;Kementerian ESDM;;\n")
            ->assertSessionHas('success');

        // Nama lokasi tidak membedakan huruf besar/kecil.
        $this->assertSame($lokasi->id, User::where('email', 'sinta@kampus.ac.id')->value('location_id'));
        // Kolom lokasi diabaikan untuk mentor.
        $this->assertNull(User::where('email', 'budi@esdm.go.id')->value('location_id'));
    }

    public function test_lokasi_tidak_dikenal_atau_nonaktif_ditolak(): void
    {
        $this->buatLokasi(['name' => 'Kantor Lama', 'is_active' => false]);

        $this->unggah("nama;email;role;lokasi\n"
            . "Sinta;sinta@kampus.ac.id;magang;Kantor Tidak Ada\n"
            . "Rudi;rudi@kampus.ac.id;magang;Kantor Lama\n")
            ->assertSessionHasErrorsIn('import');

        $pesan = session('errors')->getBag('import')->all();
        $this->assertCount(2, $pesan);
        $this->assertStringContainsString('lokasi "Kantor Tidak Ada" belum ada atau nonaktif', $pesan[0]);
        $this->assertSame(0, User::where('role', 'magang')->count());
    }

    public function test_template_dan_panduan_memuat_kolom_lokasi(): void
    {
        $this->buatLokasi(['name' => 'Kementerian ESDM']);

        $template = $this->actingAs($this->admin)->get(route('admin.users.import.template'))->getContent();
        $this->assertStringContainsString(';divisi;lokasi;email_mentor;', $template);
        $this->assertStringContainsString(';"Kementerian ESDM";', $template);

        $this->actingAs($this->admin)
            ->get(route('admin.users.import'))
            ->assertOk()
            ->assertSee('Kementerian ESDM')
            ->assertSee('peserta langsung bisa absen');
    }
}
