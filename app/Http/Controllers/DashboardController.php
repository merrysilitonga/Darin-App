<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Logbook;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Zona waktu Indonesia.
        $hariIni = Carbon::now('Asia/Jakarta')->startOfDay();

        // Nilai awal dashboard.
        $hadirTepatWaktu = 0;
        $hariBerjalan = 0;
        $totalHariMagang = 0;
        $progresPersen = 0;
        $sisaDurasi = 'Belum diatur';
        $periodeAktif = false;

        // Jumlah logbook milik mahasiswa yang sedang login.
        $jumlahLaporan = Logbook::where('user_id', $user->id)->count();

        /*
        |--------------------------------------------------------------------------
        | PERIODE MAGANG
        |--------------------------------------------------------------------------
        */

        if ($user->start_date && $user->end_date) {
            $mulai = Carbon::parse(
                $user->start_date,
                'Asia/Jakarta'
            )->startOfDay();

            $selesai = Carbon::parse(
                $user->end_date,
                'Asia/Jakarta'
            )->startOfDay();

            if ($selesai->lt($mulai)) {
                $sisaDurasi = 'Periode tidak valid';
            } else {
                // Menghitung seluruh hari kalender, termasuk tanggal awal.
                $totalHariMagang = $mulai->diffInDays($selesai) + 1;

                if ($hariIni->lt($mulai)) {
                    // Magang belum dimulai.
                    $sisaDurasi = 'Belum dimulai';
                } elseif ($hariIni->gt($selesai)) {
                    // Magang sudah selesai.
                    $hariBerjalan = $totalHariMagang;
                    $progresPersen = 100;
                    $sisaDurasi = 'Selesai';
                } else {
                    // Magang sedang berlangsung.
                    $periodeAktif = true;

                    $hariBerjalan = $mulai->diffInDays($hariIni) + 1;
                    $hariBerjalan = min(
                        $hariBerjalan,
                        $totalHariMagang
                    );

                    $progresPersen = (int) round(
                        ($hariBerjalan / $totalHariMagang) * 100
                    );

                    // Sisa durasi kalender sampai tanggal selesai.
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
                    // Batasi penghitungan hingga hari ini atau akhir magang.
                    $tanggalAkhirHitung = $hariIni->gt($selesai)
                        ? $selesai
                        : $hariIni;

                    $hadirTepatWaktu = Attendance::where(
                        'user_id',
                        $user->id
                    )
                        ->whereDate(
                            'date',
                            '>=',
                            $mulai->toDateString()
                        )
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
            'jumlahLaporan'
        ));
    }
}
