<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    /**
     * Menampilkan halaman Pengaturan Sistem.
     */
    public function edit()
    {
        $settings = SystemSetting::current();

        return view('admin.settings', compact('settings'));
    }

    /**
     * Menyimpan perubahan jadwal absensi dari Admin/HR.
     */
    public function update(Request $request)
    {
        // Validasi format seluruh pengaturan jam.
        $validated = $request->validate([
            'clock_in_start' => [
                'required',
                'date_format:H:i',
            ],

            'clock_in_end' => [
                'required',
                'date_format:H:i',
            ],

            'friday_clock_in_end' => [
                'required',
                'date_format:H:i',
            ],

            'clock_out_time' => [
                'required',
                'date_format:H:i',
            ],

            'friday_clock_out_time' => [
                'required',
                'date_format:H:i',
            ],
        ], [
            'clock_in_start.required' =>
                'Jam mulai absensi masuk wajib diisi.',
            'clock_in_start.date_format' =>
                'Format jam mulai absensi masuk tidak valid.',

            'clock_in_end.required' =>
                'Batas akhir absensi masuk Senin-Kamis wajib diisi.',
            'clock_in_end.date_format' =>
                'Format batas akhir absensi masuk Senin-Kamis tidak valid.',

            'friday_clock_in_end.required' =>
                'Batas akhir absensi masuk hari Jumat wajib diisi.',
            'friday_clock_in_end.date_format' =>
                'Format batas akhir absensi masuk hari Jumat tidak valid.',

            'clock_out_time.required' =>
                'Jam pulang Senin-Kamis wajib diisi.',
            'clock_out_time.date_format' =>
                'Format jam pulang Senin-Kamis tidak valid.',

            'friday_clock_out_time.required' =>
                'Jam pulang hari Jumat wajib diisi.',
            'friday_clock_out_time.date_format' =>
                'Format jam pulang hari Jumat tidak valid.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDASI URUTAN JAM KERJA
        |--------------------------------------------------------------------------
        |
        | Format H:i menggunakan dua digit jam dan menit.
        | Karena itu, perbandingan string dapat digunakan untuk
        | membandingkan urutan waktu dalam format 24 jam.
        |
        */

        // Jam mulai harus sebelum batas akhir masuk Senin-Kamis.
        if (
            $validated['clock_in_start'] >= $validated['clock_in_end']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'clock_in_end' =>
                        'Batas akhir absensi masuk harus setelah jam mulai absensi.',
                ]);
        }

        // Jam mulai harus sebelum batas akhir masuk hari Jumat.
        if (
            $validated['clock_in_start'] >= $validated['friday_clock_in_end']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'friday_clock_in_end' =>
                        'Batas akhir absensi masuk hari Jumat harus setelah jam mulai absensi.',
                ]);
        }

        // Jam pulang Senin-Kamis tidak boleh sebelum batas akhir masuk.
        if (
            $validated['clock_out_time'] < $validated['clock_in_end']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'clock_out_time' =>
                        'Jam pulang Senin-Kamis tidak boleh sebelum batas akhir absensi masuk.',
                ]);
        }

        // Jam pulang Jumat tidak boleh sebelum batas akhir masuk Jumat.
        if (
            $validated['friday_clock_out_time'] < $validated['friday_clock_in_end']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'friday_clock_out_time' =>
                        'Jam pulang hari Jumat tidak boleh sebelum batas akhir absensi masuk.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN PENGATURAN KE DATABASE
        |--------------------------------------------------------------------------
        */

        $settings = SystemSetting::current();

        $settings->fill($validated);
        $settings->save();

        return redirect()
            ->route('admin.settings')
            ->with(
                'success',
                'Pengaturan jam kerja berhasil disimpan.'
            );
    }
}
