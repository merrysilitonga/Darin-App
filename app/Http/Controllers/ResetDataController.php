<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Logbook;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Reset Data (Danger Zone) — khusus role admin.
 *
 * Aturan keamanan:
 * - Hanya data peserta magang yang periode magangnya SUDAH BERAKHIR.
 *   Peserta aktif / belum mulai, mentor, admin, HR, divisi, lokasi,
 *   kalender libur, dan pengaturan sistem tidak pernah tersentuh.
 * - Wajib password akun admin + mengetik kata HAPUS (dicek di server).
 * - Dijalankan dalam transaksi; file di storage dihapus setelah commit.
 * - Setiap reset dicatat di log aplikasi.
 */
class ResetDataController extends Controller
{
    public const CONFIRM_WORD = 'HAPUS';

    public function index()
    {
        $alumniIds = $this->endedInternIds();

        $counts = [
            'interns' => count($alumniIds),
            'attendances' => Attendance::whereIn('user_id', $alumniIds)->count(),
            'logbooks' => Logbook::whereIn('user_id', $alumniIds)->count(),
            'leave_requests' => LeaveRequest::whereIn('user_id', $alumniIds)->count(),
        ];

        return view('admin.reset-data', [
            'counts' => $counts,
            'confirmWord' => self::CONFIRM_WORD,
            'today' => Carbon::today('Asia/Jakarta'),
        ]);
    }

    /**
     * Hapus absensi + foto selfie milik peserta yang magangnya sudah selesai.
     */
    public function attendance(Request $request)
    {
        $this->confirmDangerousAction($request, 'attendance');

        $alumniIds = $this->endedInternIds();

        $files = [];
        $jumlah = 0;

        DB::transaction(function () use ($alumniIds, &$files, &$jumlah) {
            $query = Attendance::whereIn('user_id', $alumniIds);

            $files = $this->attendanceFiles(clone $query);
            $jumlah = $query->delete();
        });

        Storage::disk('local')->delete($files);

        $this->audit($request, 'attendance', ['interns' => count($alumniIds), 'attendances' => $jumlah, 'files' => count($files)]);

        return redirect()
            ->route('admin.reset-data.view')
            ->with('success', "{$jumlah} data absensi dan " . count($files) . ' file foto milik peserta yang magangnya sudah selesai berhasil dihapus.');
    }

    /**
     * Hapus logbook milik peserta yang magangnya sudah selesai.
     */
    public function logbooks(Request $request)
    {
        $this->confirmDangerousAction($request, 'logbooks');

        $alumniIds = $this->endedInternIds();

        $jumlah = DB::transaction(
            fn () => Logbook::whereIn('user_id', $alumniIds)->delete()
        );

        $this->audit($request, 'logbooks', ['interns' => count($alumniIds), 'logbooks' => $jumlah]);

        return redirect()
            ->route('admin.reset-data.view')
            ->with('success', "{$jumlah} logbook milik peserta yang magangnya sudah selesai berhasil dihapus.");
    }

    /**
     * Hapus akun peserta yang magangnya sudah selesai beserta seluruh datanya
     * (absensi, logbook, izin, saran) dan file foto/dokumen di storage.
     */
    public function users(Request $request)
    {
        $this->confirmDangerousAction($request, 'users');

        $alumniIds = $this->endedInternIds();

        $privateFiles = [];
        $publicFiles = [];
        $jumlah = 0;

        DB::transaction(function () use ($alumniIds, &$privateFiles, &$publicFiles, &$jumlah) {
            $privateFiles = array_merge(
                $this->attendanceFiles(Attendance::whereIn('user_id', $alumniIds)),
                LeaveRequest::whereIn('user_id', $alumniIds)
                    ->whereNotNull('document_path')
                    ->pluck('document_path')
                    ->all()
            );

            $publicFiles = User::whereIn('id', $alumniIds)
                ->whereNotNull('photo')
                ->pluck('photo')
                ->all();

            // Data relasi ikut terhapus lewat foreign key cascade.
            $jumlah = User::whereIn('id', $alumniIds)->delete();
        });

        Storage::disk('local')->delete($privateFiles);
        Storage::disk('public')->delete($publicFiles);

        $this->audit($request, 'users', [
            'interns' => $jumlah,
            'files' => count($privateFiles) + count($publicFiles),
        ]);

        return redirect()
            ->route('admin.reset-data.view')
            ->with('success', "{$jumlah} akun peserta yang magangnya sudah selesai beserta seluruh datanya berhasil dihapus.");
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * ID peserta magang yang tanggal selesai magangnya sebelum hari ini (WIB).
     */
    private function endedInternIds(): array
    {
        return User::query()
            ->where('role', 'magang')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', Carbon::today('Asia/Jakarta')->toDateString())
            ->pluck('id')
            ->all();
    }

    private function attendanceFiles(Builder $query): array
    {
        return $query->get(['photo_path', 'clock_out_photo_path'])
            ->flatMap(fn (Attendance $attendance) => [
                $attendance->photo_path,
                $attendance->clock_out_photo_path,
            ])
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Wajib password akun yang login + mengetik kata konfirmasi.
     */
    private function confirmDangerousAction(Request $request, string $target): void
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirmation' => ['required', 'string', 'in:' . self::CONFIRM_WORD],
        ], [
            'password.required' => 'Password akun Anda wajib diisi.',
            'password.current_password' => 'Password yang Anda masukkan salah.',
            'confirmation.required' => 'Ketik ' . self::CONFIRM_WORD . ' untuk konfirmasi.',
            'confirmation.in' => 'Kata konfirmasi harus diketik persis: ' . self::CONFIRM_WORD . ' (huruf besar).',
        ]);
    }

    private function audit(Request $request, string $target, array $detail): void
    {
        Log::warning('DARIN reset data dijalankan', array_merge([
            'target' => $target,
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'ip' => $request->ip(),
        ], $detail));
    }
}
