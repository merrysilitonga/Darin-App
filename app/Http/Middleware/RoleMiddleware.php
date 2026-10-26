<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
