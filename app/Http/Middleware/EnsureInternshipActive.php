<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penonaktifan otomatis peserta magang setelah periode berakhir.
 *
 * Setelah end_date lewat, akun magang masuk mode BACA SAJA:
 * - halaman (GET/HEAD) tetap bisa dibuka, termasuk riwayat & ekspor;
 * - semua aksi tulis (absen, logbook, pengajuan izin) ditolak.
 *
 * Admin/HR cukup memperpanjang end_date untuk mengaktifkan kembali,
 * tanpa job terjadwal atau kolom status tambahan.
 */
class EnsureInternshipActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->isInternshipEnded()
            && !$request->isMethodSafe()
        ) {
            $message = 'Periode magang Anda telah berakhir pada '
                . $user->end_date->locale('id')->translatedFormat('d F Y')
                . '. Akun dalam mode baca saja; hubungi Admin/HR jika periode perlu diperpanjang.';

            if ($request->expectsJson()) {
                abort(403, $message);
            }

            return redirect()
                ->route('dashboard.magang')
                ->with('error', $message);
        }

        return $next($request);
    }
}