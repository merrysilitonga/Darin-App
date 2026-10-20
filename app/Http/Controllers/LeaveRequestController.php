<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * Form pengajuan izin atau sakit.
     */
    public function create()
    {
        return view('izin.create');
    }

    /**
     * Menyimpan pengajuan mahasiswa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => [
                'required',
                'in:Sakit,Izin',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],
        ], [
            'request_type.required' => 'Pilih jenis pengajuan.',
            'request_type.in' => 'Jenis pengajuan tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' =>
                'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'reason.required' => 'Alasan pengajuan wajib diisi.',
            'reason.max' => 'Alasan maksimal 2.000 karakter.',
            'document.required' => 'Surat pendukung wajib diunggah.',
            'document.mimes' =>
                'Surat harus berformat PDF, JPG, JPEG, atau PNG.',
            'document.max' => 'Ukuran surat maksimal 2 MB.',
        ]);

        $path = $request->file('document')
            ->store('surat-izin', 'local');

        if (!$path) {
            return back()
                ->withInput()
                ->with('error', 'Dokumen gagal disimpan.');
        }

        try {
            LeaveRequest::create([
                'user_id' => $request->user()->id,
                'request_type' => $validated['request_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'document_path' => $path,
                'status' => 'Menunggu',
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            throw $e;
        }

        return redirect()
            ->route('izin.history')
            ->with(
                'success',
                'Pengajuan berhasil dikirim dan menunggu persetujuan Mentor.'
            );
    }

    /**
     * Menampilkan riwayat dan hasil pemeriksaan Mentor.
     */
    public function index(Request $request)
    {
        $requests = LeaveRequest::with([
            'reviewer:id,name',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('izin.history', compact('requests'));
    }

    /**
     * Mengunduh dokumen milik mahasiswa yang sedang login.
     */
    public function download(Request $request, int $id)
    {
        $leaveRequest = LeaveRequest::where(
            'user_id',
            $request->user()->id
        )->findOrFail($id);

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($leaveRequest->document_path),
            404,
            'Dokumen tidak ditemukan.'
        );

        return $disk->download(
            $leaveRequest->document_path
        );
    }
}