<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MentorLeaveRequestController extends Controller
{
    /**
     * Menampilkan pengajuan izin mahasiswa dalam divisi Mentor.
     */
    public function index(Request $request)
    {
        $mentor = $request->user();
        $assignmentMissing = empty($mentor->division_id);

        if ($assignmentMissing) {
            $requests = collect();
        } else {
            $requests = LeaveRequest::with('user')
                ->whereHas('user', function ($query) use ($mentor) {
                    $query->where(
                        'division_id',
                        $mentor->division_id
                    );
                })
                ->orderByRaw(
                    "CASE WHEN status = 'Menunggu' THEN 0 ELSE 1 END"
                )
                ->orderByDesc('created_at')
                ->get();
        }

        return view(
            'mentor.leave-requests',
            compact('mentor', 'requests', 'assignmentMissing')
        );
    }

    /**
     * Menyetujui pengajuan izin.
     */
    public function approve(Request $request, int $id)
    {
        $mentor = $request->user();
        $leaveRequest = $this->findAssignedRequest($mentor, $id);

        if ($leaveRequest->status !== 'Menunggu') {
            return back()->with(
                'error',
                'Hanya pengajuan berstatus Menunggu yang bisa disetujui.'
            );
        }

        DB::transaction(function () use ($leaveRequest, $mentor) {
            $lockedRequest = LeaveRequest::whereKey($leaveRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== 'Menunggu') {
                throw ValidationException::withMessages([
                    'review' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $lockedRequest->update([
                'status' => 'Disetujui',
                'reviewed_by' => $mentor->id,
                'reviewed_at' => now('Asia/Jakarta'),
                'review_notes' => null,
            ]);
        });

        return back()->with(
            'success',
            'Pengajuan izin berhasil disetujui.'
        );
    }

    /**
     * Menolak pengajuan dengan alasan yang wajib diisi.
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'review_notes' => [
                'required',
                'string',
                'max:2000',
            ],
        ], [
            'review_notes.required' =>
                'Alasan penolakan wajib diisi.',
            'review_notes.max' =>
                'Alasan penolakan maksimal 2.000 karakter.',
        ]);

        $mentor = $request->user();
        $leaveRequest = $this->findAssignedRequest($mentor, $id);

        if ($leaveRequest->status !== 'Menunggu') {
            return back()->with(
                'error',
                'Hanya pengajuan berstatus Menunggu yang bisa ditolak.'
            );
        }

        DB::transaction(function () use (
            $leaveRequest,
            $mentor,
            $validated
        ) {
            $lockedRequest = LeaveRequest::whereKey($leaveRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== 'Menunggu') {
                throw ValidationException::withMessages([
                    'review' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $lockedRequest->update([
                'status' => 'Ditolak',
                'reviewed_by' => $mentor->id,
                'reviewed_at' => now('Asia/Jakarta'),
                'review_notes' => $validated['review_notes'],
            ]);
        });

        return back()->with(
            'success',
            'Pengajuan ditolak. Alasan penolakan telah disimpan.'
        );
    }

    /**
     * Mengunduh dokumen hanya untuk pengajuan mahasiswa di divisi Mentor.
     */
    public function download(Request $request, int $id)
    {
        $mentor = $request->user();

        $leaveRequest = $this->findAssignedRequest($mentor, $id);

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($leaveRequest->document_path),
            404,
            'Dokumen pengajuan tidak ditemukan.'
        );

        return $disk->download(
            $leaveRequest->document_path
        );
    }

    /**
     * Membatasi Mentor ke mahasiswa dalam divisinya.
     */
    private function findAssignedRequest($mentor, int $id): LeaveRequest
    {
        abort_if(
            empty($mentor->division_id),
            403,
            'Akun Mentor belum ditetapkan ke divisi oleh HR/Admin.'
        );

        return LeaveRequest::whereHas('user', function ($query) use ($mentor) {
            $query->where(
                'division_id',
                $mentor->division_id
            );
        })->findOrFail($id);
    }
}