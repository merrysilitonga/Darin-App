<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Assessment;
use Illuminate\Http\Request;

class MentorAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $mentor = $request->user();

        // Ambil daftar mahasiswa bimbingan mentor ini
        $students = User::where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->orderBy('name', 'asc')
            ->get();

        // Ambil data nilai (assessment) milik mahasiswa bimbingan tersebut
        $assessments = Assessment::whereIn('user_id', $students->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('mentor.assessment', compact('mentor', 'students', 'assessments'));
    }

    public function storeOrUpdate(Request $request, $id)
    {
        $mentor = $request->user();

        // Pastikan mahasiswa tersebut benar-benar bimbingan mentor ini
        $student = User::where('id', $id)
            ->where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->firstOrFail();

        // Validasi input angka (1-100)
        $validated = $request->validate([
            'kedisiplinan' => 'required|numeric|min:0|max:100',
            'kinerja_teknis' => 'required|numeric|min:0|max:100',
            'sikap_etika' => 'required|numeric|min:0|max:100',
            'laporan' => 'required|numeric|min:0|max:100',
        ]);

        // Simpan atau update nilai di tabel assessments
        Assessment::updateOrCreate(
            ['user_id' => $student->id],
            [
                'mentor_id' => $mentor->id,
                'kedisiplinan' => $validated['kedisiplinan'],
                'kinerja_teknis' => $validated['kinerja_teknis'],
                'sikap_etika' => $validated['sikap_etika'],
                'laporan' => $validated['laporan'],
                'tanggal_penilaian' => now(), // Menyimpan waktu nilai diupdate
            ]
        );

        return back()->with('success', 'Nilai mahasiswa berhasil disimpan!');
    }
}