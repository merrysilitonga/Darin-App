<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MentorLeaveRequestController extends Controller
{
    /**
     * Menampilkan pengajuan izin/sakit mahasiswa
     * yang ditugaskan langsung kepada mentor.
     */
    public function index(Request $request)
    {
        $mentor = $request->user();

        // Ambil mahasiswa yang benar-benar ditugaskan kepada mentor.
        $studentIds = User::where('role', 'magang')
            ->where('mentor_id', $mentor->id)
            ->pluck('id');

        $assignmentMissing = $studentIds->isEmpty();

        if ($assignmentMissing) {
            $requests = collect();
        } else {
            $requests = LeaveRequest::with('user')
                ->whereIn('user_id', $studentIds)
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
     * Menyetujui pengajuan izin/sakit mahasiswa bimbingan.
     */
    public function approve(Request $request, int $id)
    {
        $mentor = $request->user();

        // Pastikan pengajuan berasal dari mahasiswa bimbingan mentor.
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

            // Periksa ulang status agar tidak diproses dua kali.
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
            'Pengajuan izin/sakit berhasil disetujui.'
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

        // Hanya boleh menolak pengajuan mahasiswa bimbingannya.
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
     * Mengunduh dokumen pengajuan mahasiswa bimbingan mentor.
     */
    public function download(Request $request, int $id)
    {
        $mentor = $request->user();

        // Pastikan hak akses diperiksa sebelum file diberikan.
        $leaveRequest = $this->findAssignedRequest($mentor, $id);

        $documentPath = $leaveRequest->document_path;

        abort_unless(
            is_string($documentPath) && $documentPath !== '',
            404,
            'Dokumen pengajuan tidak tersedia.'
        );

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($documentPath),
            404,
            'Dokumen pengajuan tidak ditemukan.'
        );

        return $disk->download($documentPath);
    }

    /**
     * Memastikan pengajuan berasal dari mahasiswa bimbingan mentor.
     */
    private function findAssignedRequest(
        $mentor,
        int $id
    ): LeaveRequest {
        return LeaveRequest::whereHas(
            'user',
            function ($query) use ($mentor) {
                $query->where('mentor_id', $mentor->id)
                    ->where('role', 'magang');
            }
        )->findOrFail($id);
    }
}