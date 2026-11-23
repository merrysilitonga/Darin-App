<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Koreksi absen pulang:
 * - Peserta mengajukan jam pulang yang benar untuk hari yang terlewat absen pulang.
 * - Mentor pembimbing menyetujui (jam pulang dicatat) atau menolak (alasan wajib).
 */
class AttendanceCorrectionController extends Controller
{
    /**
     * Peserta mengajukan koreksi jam pulang.
     */
    public function store(Request $request, int $id)
    {
        $user = $request->user();

        $attendance = Attendance::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $tanggal = substr((string) $attendance->getRawOriginal('date'), 0, 10);

        if ($attendance->clock_out || !$attendance->clock_in) {
            return back()->with('error', 'Koreksi hanya untuk absensi yang sudah absen masuk tetapi belum absen pulang.');
        }

        // Hari ini masih bisa absen pulang biasa.
        if ($tanggal >= now('Asia/Jakarta')->toDateString()) {
            return back()->with('error', 'Absen pulang hari ini dilakukan di halaman Mulai Absen, bukan lewat koreksi.');
        }

        $sudahAda = AttendanceCorrection::query()
            ->where('attendance_id', $attendance->id)
            ->whereIn('status', ['Menunggu', 'Disetujui'])
            ->exists();

        if ($sudahAda) {
            return back()->with('error', 'Koreksi untuk tanggal ini sudah diajukan.');
        }

        $jamMasuk = substr((string) $attendance->clock_in, 0, 5);

        $validated = $request->validate([
            'clock_out' => ['required', 'date_format:H:i', 'after:' . $jamMasuk],
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'clock_out.required' => 'Jam pulang wajib diisi.',
            'clock_out.date_format' => 'Format jam pulang harus JJ:MM, misalnya 16:30.',
            'clock_out.after' => 'Jam pulang harus setelah jam masuk (' . $jamMasuk . ').',
            'reason.required' => 'Alasan koreksi wajib diisi.',
            'reason.max' => 'Alasan maksimal 1.000 karakter.',
        ]);

        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_out' => $validated['clock_out'] . ':00',
            'reason' => $validated['reason'],
            'status' => 'Menunggu',
        ]);

        return back()->with('success', 'Koreksi absen pulang terkirim dan menunggu persetujuan Mentor.');
    }

    /**
     * Mentor menyetujui koreksi: jam pulang dicatat pada absensi.
     */
    public function approve(Request $request, int $id)
    {
        $mentor = $request->user();
        $koreksi = $this->koreksiBimbingan($mentor, $id);

        if ($koreksi->status !== 'Menunggu') {
            return back()->with('error', 'Koreksi ini sudah diproses.');
        }

        $berhasil = DB::transaction(function () use ($koreksi, $mentor) {
            $terkunci = AttendanceCorrection::whereKey($koreksi->id)->lockForUpdate()->firstOrFail();
            $attendance = Attendance::whereKey($terkunci->attendance_id)->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== 'Menunggu' || $attendance->clock_out) {
                return false;
            }

            $attendance->update([
                'clock_out' => $terkunci->clock_out,
                'clock_out_location_name' => 'Koreksi disetujui mentor',
            ]);

            $terkunci->update([
                'status' => 'Disetujui',
                'reviewed_by' => $mentor->id,
                'reviewed_at' => now('Asia/Jakarta'),
                'review_notes' => null,
            ]);

            return true;
        });

        return $berhasil
            ? back()->with('success', 'Koreksi disetujui. Jam pulang ' . $koreksi->jamPulang() . ' sudah dicatat.')
            : back()->with('error', 'Koreksi ini sudah diproses atau absen pulang sudah tercatat.');
    }

    /**
     * Mentor menolak koreksi dengan alasan.
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:2000'],
        ], [
            'review_notes.required' => 'Alasan penolakan wajib diisi.',
            'review_notes.max' => 'Alasan penolakan maksimal 2.000 karakter.',
        ]);

        $mentor = $request->user();
        $koreksi = $this->koreksiBimbingan($mentor, $id);

        if ($koreksi->status !== 'Menunggu') {
            return back()->with('error', 'Koreksi ini sudah diproses.');
        }

        $koreksi->update([
            'status' => 'Ditolak',
            'reviewed_by' => $mentor->id,
            'reviewed_at' => now('Asia/Jakarta'),
            'review_notes' => $validated['review_notes'],
        ]);

        return back()->with('success', 'Koreksi ditolak. Alasan penolakan telah disimpan.');
    }

    /** Koreksi milik mahasiswa bimbingan mentor ini (404 jika bukan). */
    private function koreksiBimbingan(User $mentor, int $id): AttendanceCorrection
    {
        return AttendanceCorrection::query()
            ->whereKey($id)
            ->whereHas('user', function ($query) use ($mentor) {
                $query->where('role', 'magang')->where('mentor_id', $mentor->id);
            })
            ->firstOrFail();
    }
}
