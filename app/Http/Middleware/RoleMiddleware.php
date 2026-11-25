<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        // Pastikan pengguna sudah login.
        if (!$user) {
            abort(403, 'Anda tidak mempunyai akses ke halaman ini.');
        }

        // Akun yang belum disetujui / dinonaktifkan langsung dikeluarkan.
        if ($user->isPendingApproval()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda masih menunggu persetujuan Admin DARIN. Silakan coba login kembali setelah disetujui.',
                ]);
        }

        if (!$user->isActive()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Hubungi Admin/HR DARIN.',
                ]);
        }

        // Normalisasi role pengguna.
        $userRole = strtolower(trim((string) $user->role));

        // Normalisasi seluruh role yang diizinkan.
        $allowedRoles = array_map(
            fn ($role) => strtolower(trim($role)),
            $roles
        );

        // Pastikan role pengguna diizinkan.
        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Anda tidak mempunyai akses ke halaman ini.');
        }

        return $next($request);
    }
}
