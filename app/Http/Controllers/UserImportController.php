<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Location;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Impor akun Peserta Magang & Mentor dari file CSV oleh Admin/HR.
 *
 * Kolom (header wajib, urutan bebas):
 * nama;email;role;nim;universitas;prodi;telepon;nip;jabatan;divisi;lokasi;email_mentor;tanggal_mulai;tanggal_selesai;password
 *
 * - role: magang atau mentor (akun admin tidak bisa diimpor).
 * - divisi: nama divisi yang sudah ada (tidak membedakan huruf besar/kecil).
 * - lokasi: khusus magang, nama lokasi absensi yang aktif → peserta langsung bisa absen.
 * - email_mentor: email mentor yang sudah ada atau ikut diimpor di file yang sama.
 * - password kosong → dibuatkan otomatis dan ditampilkan sekali setelah impor.
 * - Semua baris divalidasi dulu; jika ada satu yang salah, tidak ada yang disimpan.
 */
class UserImportController extends Controller
{
    private const MAX_ROWS = 300;

    private const DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y'];

    /** Nama kolom yang diterima → kolom standar. */
    private const HEADER_ALIASES = [
        'nama' => 'nama', 'name' => 'nama', 'nama lengkap' => 'nama',
        'email' => 'email',
        'role' => 'role', 'peran' => 'role', 'jenis akun' => 'role',
        'nim' => 'nim',
        'universitas' => 'universitas', 'kampus' => 'universitas', 'university' => 'universitas',
        'prodi' => 'prodi', 'program studi' => 'prodi', 'jurusan' => 'prodi',
        'telepon' => 'telepon', 'no hp' => 'telepon', 'hp' => 'telepon', 'phone' => 'telepon', 'whatsapp' => 'telepon',
        'nip' => 'nip',
        'jabatan' => 'jabatan', 'position' => 'jabatan',
        'divisi' => 'divisi', 'division' => 'divisi', 'unit' => 'divisi',
        'lokasi' => 'lokasi', 'lokasi kerja' => 'lokasi', 'lokasi absen' => 'lokasi', 'lokasi absensi' => 'lokasi', 'location' => 'lokasi', 'kantor' => 'lokasi',
        'email mentor' => 'email_mentor', 'email_mentor' => 'email_mentor', 'mentor' => 'email_mentor',
        'tanggal mulai' => 'tanggal_mulai', 'tanggal_mulai' => 'tanggal_mulai', 'mulai' => 'tanggal_mulai',
        'tanggal selesai' => 'tanggal_selesai', 'tanggal_selesai' => 'tanggal_selesai', 'selesai' => 'tanggal_selesai',
        'password' => 'password', 'kata sandi' => 'password',
    ];

    public function create()
    {
        return view('admin.users-import', [
            'maxRows' => self::MAX_ROWS,
            'divisions' => Division::orderBy('name')->pluck('name'),
            'locations' => Location::where('is_active', true)->orderBy('name')->pluck('name'),
        ]);
    }

    public function template()
    {
        // Contoh lokasi memakai nama lokasi aktif yang benar-benar ada.
        $contohLokasi = Location::where('is_active', true)->orderBy('name')->value('name') ?? 'Kementerian ESDM';

        $rows = [
            ['nama', 'email', 'role', 'nim', 'universitas', 'prodi', 'telepon', 'nip', 'jabatan', 'divisi', 'lokasi', 'email_mentor', 'tanggal_mulai', 'tanggal_selesai', 'password'],
            ['Budi Santoso', 'budi.mentor@esdm.go.id', 'mentor', '', '', '', '081234567890', '198701012010011001', 'Analis Data', 'IT Prasarana', '', '', '', '', ''],
            ['Sinta Rahmawati', 'sinta@mahasiswa.ac.id', 'magang', '2101234567', 'Universitas Contoh', 'Sistem Informasi', '081298765432', '', '', 'IT Prasarana', $contohLokasi, 'budi.mentor@esdm.go.id', '2026-11-01', '2026-12-31', ''],
        ];

        $stream = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($stream, $row, ';', '"', '');
        }

        rewind($stream);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-impor-pengguna.csv"',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.required' => 'File CSV wajib dipilih.',
            'file.mimes' => 'File harus berformat .csv.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $rows = $this->readCsv((string) file_get_contents($request->file('file')->getRealPath()));

        if (is_string($rows)) {
            return $this->failed([$rows]);
        }

        [$data, $errors] = $this->validateRows($rows);

        if ($errors !== []) {
            return $this->failed($errors);
        }

        if ($data === []) {
            return $this->failed(['Tidak ada baris data yang dapat diimpor.']);
        }

        $credentials = [];

        DB::transaction(function () use ($data, &$credentials) {
            // Mentor dibuat lebih dulu agar bisa dipakai peserta di file yang sama.
            usort($data, fn ($a, $b) => ($a['role'] === 'mentor' ? 0 : 1) <=> ($b['role'] === 'mentor' ? 0 : 1));

            $mentorIds = [];

            foreach ($data as $row) {
                $passwordGenerated = $row['password'] === '';
                $password = $passwordGenerated ? $this->generatePassword() : $row['password'];

                $user = new User();
                $user->name = $row['nama'];
                $user->email = $row['email'];
                $user->password = Hash::make($password);
                $user->role = $row['role'];
                $user->phone = $row['telepon'] ?: null;
                $user->division_id = $row['division_id'];

                if ($row['role'] === 'magang') {
                    $user->nim = $row['nim'] ?: null;
                    $user->university = $row['universitas'] ?: null;
                    $user->study_program = $row['prodi'] ?: null;
                    $user->location_id = $row['location_id'];
                    $user->start_date = $row['tanggal_mulai'];
                    $user->end_date = $row['tanggal_selesai'];
                    $user->mentor_id = $row['email_mentor'] !== ''
                        ? ($mentorIds[$row['email_mentor']] ?? User::where('email', $row['email_mentor'])->value('id'))
                        : null;
                } else {
                    $user->nip = $row['nip'] ?: null;
                    $user->position = $row['jabatan'] ?: null;
                }

                $user->save();

                if ($row['role'] === 'mentor') {
                    $mentorIds[$row['email']] = $user->id;
                }

                $credentials[] = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    // Hanya password yang dibuat sistem yang ditampilkan.
                    'password' => $passwordGenerated ? $password : null,
                ];
            }
        });

        Log::info('DARIN impor pengguna', [
            'admin_id' => $request->user()->id,
            'jumlah' => count($credentials),
        ]);

        return redirect()
            ->route('admin.users.import')
            ->with('success', count($credentials) . ' akun berhasil diimpor.')
            ->with('import_credentials', $credentials);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * Baca CSV → daftar [nomor_baris => [kolom_standar => nilai]] atau pesan error.
     */
    private function readCsv(string $content): array|string
    {
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        if (trim($content) === '') {
            return 'File CSV kosong.';
        }

        $barisPertama = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($barisPertama, ';') >= substr_count($barisPertama, ',') ? ';' : ',';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $header = null;
        $rows = [];
        $nomor = 0;

        while (($kolom = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
            $nomor++;
            $kolom = array_map(fn ($nilai) => trim((string) $nilai), $kolom);

            if (implode('', $kolom) === '') {
                continue;
            }

            if ($header === null) {
                $header = array_map(
                    fn ($nama) => self::HEADER_ALIASES[mb_strtolower(str_replace(['_', '-'], ' ', $nama))]
                        ?? self::HEADER_ALIASES[mb_strtolower($nama)]
                        ?? null,
                    $kolom
                );

                foreach (['nama', 'email', 'role'] as $wajib) {
                    if (!in_array($wajib, $header, true)) {
                        fclose($stream);

                        return "Baris header wajib memuat kolom: nama, email, role. Unduh template untuk contoh.";
                    }
                }

                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                fclose($stream);

                return 'Maksimal ' . self::MAX_ROWS . ' baris data per file.';
            }

            $row = [];

            foreach ($header as $i => $nama) {
                if ($nama !== null) {
                    $row[$nama] = $kolom[$i] ?? '';
                }
            }

            $rows[$nomor] = $row;
        }

        fclose($stream);

        return $rows;
    }

    /**
     * Validasi semua baris. Mengembalikan [data siap simpan, daftar error].
     */
    private function validateRows(array $rows): array
    {
        $kolom = ['nama', 'email', 'role', 'nim', 'universitas', 'prodi', 'telepon', 'nip', 'jabatan', 'divisi', 'lokasi', 'email_mentor', 'tanggal_mulai', 'tanggal_selesai', 'password'];

        $divisions = Division::all(['id', 'name'])
            ->mapWithKeys(fn (Division $division) => [mb_strtolower(trim($division->name)) => $division->id]);

        // Hanya lokasi aktif yang boleh menjadi penempatan baru.
        $locations = Location::where('is_active', true)->get(['id', 'name'])
            ->mapWithKeys(fn (Location $location) => [mb_strtolower(trim($location->name)) => $location->id]);

        $existingEmails = User::pluck('email')->map(fn ($email) => mb_strtolower($email))->flip();
        $existingMentors = User::where('role', 'mentor')->pluck('email')->map(fn ($email) => mb_strtolower($email))->flip();

        // Mentor yang ikut diimpor di file ini.
        $fileMentors = [];

        foreach ($rows as $row) {
            if (mb_strtolower($row['role'] ?? '') === 'mentor') {
                $fileMentors[mb_strtolower($row['email'] ?? '')] = true;
            }
        }

        $data = [];
        $errors = [];
        $emailDiFile = [];

        foreach ($rows as $nomor => $row) {
            $row = array_merge(array_fill_keys($kolom, ''), $row);
            $prefix = "Baris {$nomor}: ";
            $salah = [];

            $row['email'] = mb_strtolower($row['email']);
            $row['role'] = mb_strtolower($row['role']);
            $row['email_mentor'] = mb_strtolower($row['email_mentor']);

            if ($row['nama'] === '' || mb_strlen($row['nama']) > 255) {
                $salah[] = 'nama wajib diisi (maks. 255 karakter)';
            }

            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $salah[] = "email \"{$row['email']}\" tidak valid";
            } elseif ($existingEmails->has($row['email'])) {
                $salah[] = "email {$row['email']} sudah terdaftar";
            } elseif (isset($emailDiFile[$row['email']])) {
                $salah[] = "email {$row['email']} duplikat dengan baris {$emailDiFile[$row['email']]}";
            }

            if (!in_array($row['role'], ['magang', 'mentor'], true)) {
                $salah[] = 'role harus "magang" atau "mentor"';
            }

            $row['division_id'] = null;

            if ($row['divisi'] !== '') {
                $row['division_id'] = $divisions[mb_strtolower($row['divisi'])] ?? null;

                if ($row['division_id'] === null) {
                    $salah[] = "divisi \"{$row['divisi']}\" belum ada (buat dulu di menu Penempatan & Divisi)";
                }
            }

            if ($row['password'] !== '' && mb_strlen($row['password']) < 8) {
                $salah[] = 'password minimal 8 karakter (atau kosongkan agar dibuat otomatis)';
            }

            foreach (['nim' => 50, 'nip' => 50, 'telepon' => 30, 'universitas' => 255, 'prodi' => 255, 'jabatan' => 255] as $field => $max) {
                if (mb_strlen($row[$field]) > $max) {
                    $salah[] = "{$field} maksimal {$max} karakter";
                }
            }

            if ($row['role'] === 'magang') {
                $mulai = $this->parseDate($row['tanggal_mulai']);
                $selesai = $this->parseDate($row['tanggal_selesai']);

                if ($row['tanggal_mulai'] !== '' && $mulai === null) {
                    $salah[] = 'tanggal_mulai tidak valid (YYYY-MM-DD atau DD/MM/YYYY)';
                }

                if ($row['tanggal_selesai'] !== '' && $selesai === null) {
                    $salah[] = 'tanggal_selesai tidak valid (YYYY-MM-DD atau DD/MM/YYYY)';
                }

                if (($mulai === null) !== ($selesai === null) && $salah === []) {
                    $salah[] = 'tanggal_mulai dan tanggal_selesai harus diisi keduanya atau dikosongkan keduanya';
                }

                if ($mulai && $selesai && $selesai < $mulai) {
                    $salah[] = 'tanggal_selesai tidak boleh sebelum tanggal_mulai';
                }

                $row['tanggal_mulai'] = $mulai;
                $row['tanggal_selesai'] = $selesai;

                $row['location_id'] = null;

                if ($row['lokasi'] !== '') {
                    $row['location_id'] = $locations[mb_strtolower($row['lokasi'])] ?? null;

                    if ($row['location_id'] === null) {
                        $salah[] = "lokasi \"{$row['lokasi']}\" belum ada atau nonaktif (cek menu Lokasi Absensi)";
                    }
                }

                if ($row['email_mentor'] !== ''
                    && !$existingMentors->has($row['email_mentor'])
                    && !isset($fileMentors[$row['email_mentor']])) {
                    $salah[] = "mentor dengan email {$row['email_mentor']} tidak ditemukan";
                }
            } else {
                $row['tanggal_mulai'] = null;
                $row['tanggal_selesai'] = null;
                $row['email_mentor'] = '';
                $row['location_id'] = null;
            }

            if ($salah !== []) {
                $errors[] = $prefix . implode('; ', $salah) . '.';
                continue;
            }

            $emailDiFile[$row['email']] = $nomor;
            $data[] = $row;
        }

        return [$data, array_slice($errors, 0, 30)];
    }

    private function parseDate(string $nilai): ?string
    {
        $nilai = trim($nilai);

        if ($nilai === '') {
            return null;
        }

        foreach (self::DATE_FORMATS as $format) {
            $tanggal = DateTime::createFromFormat('!' . $format, $nilai);

            if ($tanggal !== false && $tanggal->format($format) === $nilai) {
                return $tanggal->format('Y-m-d');
            }
        }

        return null;
    }

    private function generatePassword(): string
    {
        // 10 karakter tanpa huruf yang mirip (0/O, 1/l) agar mudah dibagikan.
        $karakter = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $hasil = '';

        for ($i = 0; $i < 10; $i++) {
            $hasil .= $karakter[random_int(0, strlen($karakter) - 1)];
        }

        return $hasil;
    }

    private function failed(array $errors)
    {
        return redirect()
            ->route('admin.users.import')
            ->withErrors($errors, 'import');
    }
}
