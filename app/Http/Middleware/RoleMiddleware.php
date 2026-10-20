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

        // Pastikan pengguna sudah login dan memiliki role yang sesuai.
        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak mempunyai akses ke halaman ini.');
        }

        return $next($request);
    }
}
