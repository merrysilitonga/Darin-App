<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * Form pengajuan izin atau sakit.
     */
    public function create()
    {
        // Form pengajuan kini menyatu di halaman Riwayat Absensi & Izin.
        return redirect()->to(route('riwayat.absen', ['ajukan' => 1]) . '#form-izin');
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

        /*
         * Tanggal pengajuan harus berada di dalam periode magang,
         * agar tidak ada izin/sakit di luar masa kewajiban hadir.
         */
        $user = $request->user();

        if (!$user->start_date || !$user->end_date) {
            return back()
                ->withInput()
                ->with('error', 'Periode magang belum ditentukan oleh HR/Admin.');
        }

        $periodStart = $user->start_date->toDateString();
        $periodEnd = $user->end_date->toDateString();

        // Normalisasi ke Y-m-d agar perbandingan string akurat.
        $requestStart = Carbon::parse($validated['start_date'])->toDateString();
        $requestEnd = Carbon::parse($validated['end_date'])->toDateString();

        if ($requestStart < $periodStart || $requestEnd > $periodEnd) {
            return back()
                ->withInput()
                ->withErrors([
                    'start_date' => "Tanggal pengajuan harus di dalam periode magang ({$periodStart} sampai {$periodEnd}).",
                ]);
        }

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

        // Riwayat izin kini digabung dengan riwayat absensi (bulan tanggal mulai izin).
        return redirect()
            ->to(route('riwayat.absen', [
                'bulan' => Carbon::parse($validated['start_date'])->format('Y-m'),
            ]) . '#riwayat-izin')
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
        // Halaman riwayat izin digabung ke Riwayat Absensi & Izin.
        // Route lama tetap ada agar tautan/bookmark lama tidak rusak.
        $parameter = [];

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('bulan'))) {
            $parameter['bulan'] = $request->query('bulan');
        }

        return redirect()->to(route('riwayat.absen', $parameter) . '#riwayat-izin');
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