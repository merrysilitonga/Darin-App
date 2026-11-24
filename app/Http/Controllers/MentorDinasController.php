<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Persetujuan absen Dinas Luar oleh Mentor pembimbing.
 * - Disetujui: absensi dihitung Hadir/Terlambat seperti biasa.
 * - Ditolak (alasan wajib): hari itu dihitung Alfa di rekap.
 * Absen pulang tetap dilakukan peserta, tidak bergantung pada persetujuan.
 */
class MentorDinasController extends Controller
{
    public function approve(Request $request, int $id)
    {
        $mentor = $request->user();
        $attendance = $this->dinasBimbingan($mentor, $id);

        $berhasil = DB::transaction(function () use ($attendance, $mentor) {
            $terkunci = Attendance::whereKey($attendance->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->dinas_status !== 'Menunggu') {
                return false;
            }

            $terkunci->update([
                'dinas_status' => 'Disetujui',
                'dinas_reviewed_by' => $mentor->id,
                'dinas_reviewed_at' => now('Asia/Jakarta'),
                'dinas_review_notes' => null,
            ]);

            return true;
        });

        return $berhasil
            ? back()->with('success', 'Dinas Luar ' . ($attendance->user?->name ?? 'peserta') . ' disetujui.')
            : back()->with('error', 'Dinas Luar ini sudah diproses.');
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'dinas_review_notes' => ['required', 'string', 'max:2000'],
        ], [
            'dinas_review_notes.required' => 'Alasan penolakan wajib diisi.',
            'dinas_review_notes.max' => 'Alasan penolakan maksimal 2.000 karakter.',
        ]);

        $mentor = $request->user();
        $attendance = $this->dinasBimbingan($mentor, $id);

        $berhasil = DB::transaction(function () use ($attendance, $mentor, $validated) {
            $terkunci = Attendance::whereKey($attendance->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->dinas_status !== 'Menunggu') {
                return false;
            }

            $terkunci->update([
                'dinas_status' => 'Ditolak',
                'dinas_reviewed_by' => $mentor->id,
                'dinas_reviewed_at' => now('Asia/Jakarta'),
                'dinas_review_notes' => $validated['dinas_review_notes'],
            ]);

            return true;
        });

        return $berhasil
            ? back()->with('success', 'Dinas Luar ditolak. Hari tersebut tidak dihitung hadir.')
            : back()->with('error', 'Dinas Luar ini sudah diproses.');
    }

    /** Foto selfie absen Dinas Luar (?type=pulang untuk absen pulang). */
    public function photo(Request $request, int $id)
    {
        $attendance = $this->dinasBimbingan($request->user(), $id);

        $path = $request->query('type') === 'pulang'
            ? $attendance->clock_out_photo_path
            : $attendance->photo_path;

        abort_unless($path, 404, 'Foto absensi tidak tersedia.');

        $disk = Storage::disk('local')->exists($path) ? 'local' : 'public';

        abort_unless(Storage::disk($disk)->exists($path), 404, 'File foto tidak ditemukan.');

        return Storage::disk($disk)->response(
            $path,
            null,
            ['Cache-Control' => 'private, no-store, max-age=0'],
            'inline'
        );
    }

    /** Absensi Dinas Luar milik mahasiswa bimbingan mentor ini (404 jika bukan). */
    private function dinasBimbingan(User $mentor, int $id): Attendance
    {
        return Attendance::query()
            ->whereKey($id)
            ->where('work_location', 'Dinas Luar')
            ->whereHas('user', function ($query) use ($mentor) {
                $query->where('role', 'magang')->where('mentor_id', $mentor->id);
            })
            ->with('user')
            ->firstOrFail();
    }
}
