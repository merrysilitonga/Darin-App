<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Logbook;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Mentor pembimbing yang ditetapkan Admin/HR (beserta divisinya).
        $user->loadMissing('mentor.division');
        $mentor = $user->mentor;
        $mentorWhatsapp = $this->whatsappNumber($mentor?->phone);

        // Gunakan tanggal hari ini berdasarkan zona waktu Indonesia.
        $hariIni = Carbon::today('Asia/Jakarta');

        // Nilai awal dashboard.
        $hadirTepatWaktu = 0;
        $hariBerjalan = 0;
        $totalHariMagang = 0;
        $progresPersen = 0;
        $sisaDurasi = 'Belum diatur';
        $periodeAktif = false;

        // Jumlah logbook milik mahasiswa yang sedang login.
        $jumlahLaporan = Logbook::where('user_id', $user->id)->count();

        // Informasi kalender hari libur untuk peserta.
        $todayHoliday = Holiday::activeOn($hariIni->toDateString());
        $upcomingHolidays = Holiday::upcoming($hariIni, 30, 3);

        /*
        |--------------------------------------------------------------------------
        | PERIODE MAGANG
        |--------------------------------------------------------------------------
        */

        /*
         * Ambil tanggal mentah dari database agar parsing tanggal
         * dilakukan langsung menggunakan zona waktu Asia/Jakarta.
         */
        $tanggalMulai = $user->getRawOriginal('start_date');
        $tanggalSelesai = $user->getRawOriginal('end_date');

        if ($tanggalMulai && $tanggalSelesai) {
            $mulai = Carbon::parse(
                $tanggalMulai,
                'Asia/Jakarta'
            )->startOfDay();

            $selesai = Carbon::parse(
                $tanggalSelesai,
                'Asia/Jakarta'
            )->startOfDay();

            if ($selesai->lt($mulai)) {
                $sisaDurasi = 'Periode tidak valid';
            } else {
                // Menghitung hari kalender, termasuk tanggal mulai.
                $totalHariMagang = (int) $mulai->diffInDays($selesai) + 1;

                if ($hariIni->lt($mulai)) {
                    // Periode magang belum dimulai.
                    $sisaDurasi = 'Belum dimulai';
                } elseif ($hariIni->gt($selesai)) {
                    // Periode magang sudah berakhir.
                    $hariBerjalan = $totalHariMagang;
                    $progresPersen = 100;
                    $sisaDurasi = 'Selesai';
                } else {
                    // Periode magang sedang berlangsung.
                    $periodeAktif = true;

                    $hariBerjalan = (int) $mulai->diffInDays($hariIni) + 1;
                    $hariBerjalan = min(
                        $hariBerjalan,
                        $totalHariMagang
                    );

                    $progresPersen = (int) round(
                        ($hariBerjalan / $totalHariMagang) * 100
                    );

                    // Menghitung sisa kalender berdasarkan tanggal selesai.
                    $selisih = $hariIni->diff($selesai);

                    $bulan = ($selisih->y * 12) + $selisih->m;
                    $hari = $selisih->d;

                    if ($bulan > 0 && $hari > 0) {
                        $sisaDurasi = "{$bulan} Bulan {$hari} Hari";
                    } elseif ($bulan > 0) {
                        $sisaDurasi = "{$bulan} Bulan";
                    } elseif ($hari > 0) {
                        $sisaDurasi = "{$hari} Hari";
                    } else {
                        $sisaDurasi = 'Hari terakhir';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | KEHADIRAN
                |--------------------------------------------------------------------------
                */

                if ($hariIni->gte($mulai)) {
                    // Penghitungan dibatasi sampai hari ini atau akhir magang.
                    $tanggalAkhirHitung = $hariIni->gt($selesai)
                        ? $selesai
                        : $hariIni;

                    $hadirTepatWaktu = Attendance::where(
                        'user_id',
                        $user->id
                    )
                        ->whereDate('date', '>=', $mulai->toDateString())
                        ->whereDate(
                            'date',
                            '<=',
                            $tanggalAkhirHitung->toDateString()
                        )
                        ->where('status', 'Hadir')
                        ->count();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', compact(
            'user',
            'hadirTepatWaktu',
            'hariBerjalan',
            'totalHariMagang',
            'progresPersen',
            'sisaDurasi',
            'periodeAktif',
            'jumlahLaporan',
            'mentor',
            'mentorWhatsapp',
            'todayHoliday',
            'upcomingHolidays'
        ));
    }

    /**
     * Mengubah nomor HP Indonesia ke format wa.me (contoh: 0812-3456 → 628123456).
     * Mengembalikan null jika nomor kosong atau tidak valid.
     */
    private function whatsappNumber(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        // Nomor Indonesia valid: diawali 62 dan panjang wajar.
        if (!str_starts_with($digits, '62') || strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }
}
