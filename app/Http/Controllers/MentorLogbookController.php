<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MentorLogbookController extends Controller
{
    /**
     * Menampilkan logbook mahasiswa di divisi mentor.
     */
    public function index(Request $request)
    {
        $mentor = $request->user();

        $assignmentMissing = empty($mentor->division_id);

        if ($assignmentMissing) {
            $logbooks = collect();
        } else {
            $logbooks = Logbook::with('user')
                ->whereHas('user', function ($query) use ($mentor) {
                    $query->where(
                        'division_id',
                        $mentor->division_id
                    );
                })
                ->orderByRaw("CASE WHEN status = 'Menunggu' THEN 0 ELSE 1 END")
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
     * Menyetujui logbook mahasiswa.
     */
    public function approve(Request $request, int $id)
    {
        $mentor = $request->user();

        $logbook = $this->findAssignedLogbook($mentor, $id);

        if ($logbook->status !== 'Menunggu') {
            return back()->with(
                'error',
                'Hanya logbook berstatus Menunggu yang bisa disetujui.'
            );
        }

        DB::transaction(function () use ($logbook, $mentor) {
            // Periksa ulang status di database untuk menghindari
            // pemrosesan ulang catatan yang sudah direview.
            $lockedLogbook = Logbook::whereKey($logbook->id)
                ->lockForUpdate()
                ->firstOrFail();

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
     * Pastikan mentor hanya memproses logbook dari divisinya.
     */
    private function findAssignedLogbook($mentor, int $id): Logbook
    {
        abort_if(
            empty($mentor->division_id),
            403,
            'Akun mentor belum ditetapkan ke divisi oleh admin.'
        );

        return Logbook::whereHas('user', function ($query) use ($mentor) {
            $query->where(
                'division_id',
                $mentor->division_id
            );
        })->findOrFail($id);
    }
}