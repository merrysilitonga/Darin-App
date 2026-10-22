<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MentorLogbookController extends Controller
{
    /**
     * Menampilkan logbook mahasiswa yang ditugaskan kepada mentor.
     */
    public function index(Request $request)
    {
        $mentor = $request->user();

        // Ambil hanya mahasiswa yang ditugaskan langsung kepada mentor ini.
        $studentIds = User::whereIn('role', ['magang', 'intern'])
            ->where('mentor_id', $mentor->id)
            ->pluck('id');

        // Status ini dipakai view untuk menampilkan kondisi kosong.
        $assignmentMissing = $studentIds->isEmpty();

        if ($assignmentMissing) {
            $logbooks = collect();
        } else {
            $logbooks = Logbook::with('user')
                ->whereIn('user_id', $studentIds)
                ->orderByRaw(
                    "CASE WHEN status = 'Menunggu' THEN 0 ELSE 1 END"
                )
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
        }

        return view(
            'mentor.logbook',
            compact('mentor', 'logbooks', 'assignmentMissing')
        );
    }

    /**
     * Menyetujui logbook mahasiswa bimbingan.
     */
    public function approve(Request $request, int $id)
    {
        $mentor = $request->user();

        // Pastikan logbook benar-benar milik mahasiswa bimbingan mentor.
        $logbook = $this->findAssignedLogbook($mentor, $id);

        if ($logbook->status !== 'Menunggu') {
            return back()->with(
                'error',
                'Hanya logbook berstatus Menunggu yang bisa disetujui.'
            );
        }

        DB::transaction(function () use ($logbook, $mentor) {
            $lockedLogbook = Logbook::whereKey($logbook->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Periksa ulang status agar tidak diproses dua kali.
            if ($lockedLogbook->status !== 'Menunggu') {
                throw ValidationException::withMessages([
                    'review' => 'Logbook ini sudah diproses oleh reviewer lain.',
                ]);
            }

            $lockedLogbook->update([
                'status' => 'Disetujui',
                'mentor_feedback' => null,
                'reviewed_by' => $mentor->id,
                'reviewed_at' => now('Asia/Jakarta'),
            ]);
        });

        return back()->with(
            'success',
            'Logbook berhasil disetujui.'
        );
    }

    /**
     * Menolak logbook dan menyimpan catatan revisi.
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'mentor_feedback' => [
                'required',
                'string',
                'max:2000',
            ],
        ], [
            'mentor_feedback.required' =>
                'Catatan revisi wajib diisi ketika menolak logbook.',
            'mentor_feedback.max' =>
                'Catatan revisi maksimal 2.000 karakter.',
        ]);

        $mentor = $request->user();

        // Pastikan mentor hanya menolak logbook mahasiswa bimbingannya.
        $logbook = $this->findAssignedLogbook($mentor, $id);

        if ($logbook->status !== 'Menunggu') {
            return back()->with(
                'error',
                'Hanya logbook berstatus Menunggu yang bisa ditolak.'
            );
        }

        DB::transaction(function () use ($logbook, $mentor, $validated) {
            $lockedLogbook = Logbook::whereKey($logbook->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLogbook->status !== 'Menunggu') {
                throw ValidationException::withMessages([
                    'review' => 'Logbook ini sudah diproses oleh reviewer lain.',
                ]);
            }

            $lockedLogbook->update([
                'status' => 'Ditolak',
                'mentor_feedback' => $validated['mentor_feedback'],
                'reviewed_by' => $mentor->id,
                'reviewed_at' => now('Asia/Jakarta'),
            ]);
        });

        return back()->with(
            'success',
            'Logbook ditolak. Catatan revisi sudah dikirim kepada mahasiswa.'
        );
    }

    /**
     * Memastikan logbook milik mahasiswa yang ditugaskan kepada mentor.
     *
     * Pengecekan ini berlaku untuk halaman daftar, persetujuan,
     * dan penolakan agar mentor tidak dapat memproses logbook
     * mahasiswa yang ditugaskan kepada mentor lain.
     */
    private function findAssignedLogbook($mentor, int $id): Logbook
    {
        return Logbook::whereHas('user', function ($query) use ($mentor) {
            $query->where('mentor_id', $mentor->id)
                ->whereIn('role', ['magang', 'intern']);
        })->findOrFail($id);
    }
}
