<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function edit()
    {
        $settings = SystemSetting::current();

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clock_in_start' => ['required', 'date_format:H:i'],
            'clock_in_end' => ['required', 'date_format:H:i'],
            'friday_clock_in_end' => ['required', 'date_format:H:i'],
            'clock_out_time' => ['required', 'date_format:H:i'],
            'friday_clock_out_time' => ['required', 'date_format:H:i'],
        ], [
            '*.required' => 'Semua jam kerja wajib diisi.',
            '*.date_format' => 'Format jam harus valid.',
        ]);

        // Karena formatnya HH:MM, perbandingan string aman untuk membandingkan waktu.
        if ($validated['clock_in_start'] >= $validated['clock_in_end']) {
            return back()
                ->withInput()
                ->withErrors([
                    'clock_in_end' => 'Batas akhir absensi masuk harus setelah jam mulai absensi.',
                ]);
        }

        if ($validated['clock_in_start'] >= $validated['friday_clock_in_end']) {
            return back()
                ->withInput()
                ->withErrors([
                    'friday_clock_in_end' => 'Batas akhir absensi masuk hari Jumat harus setelah jam mulai absensi.',
                ]);
        }

        if ($validated['clock_out_time'] < $validated['clock_in_end']) {
            return back()
                ->withInput()
                ->withErrors([
                    'clock_out_time' => 'Jam absensi pulang Senin–Kamis tidak boleh sebelum batas akhir absensi masuk.',
                ]);
        }

        if ($validated['friday_clock_out_time'] < $validated['friday_clock_in_end']) {
            return back()
                ->withInput()
                ->withErrors([
                    'friday_clock_out_time' => 'Jam absensi pulang hari Jumat tidak boleh sebelum batas akhir absensi masuk.',
                ]);
        }

        $settings = SystemSetting::current();
        $settings->fill($validated);
        $settings->save();

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Pengaturan jam kerja berhasil disimpan.');
    }
}
