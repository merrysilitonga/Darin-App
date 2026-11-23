<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pengelolaan akun oleh Admin/HR: ubah data akun & nonaktif/aktifkan akun.
 * Aturan akses sama dengan reset password:
 * - akun sendiri diubah lewat halaman Profil,
 * - HR tidak boleh mengubah akun Admin/HR.
 */
class AdminUserAccountController extends Controller
{
    /**
     * Ubah data akun (role tidak diubah di sini).
     */
    public function update(Request $request, User $user)
    {
        if ($tolak = $this->tolakJikaTidakBerhak($request, $user)) {
            return $tolak;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nim' => ['nullable', 'string', 'max:30'],
            'university' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'nip' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
        ]);

        // Kolom yang tidak relevan dengan role-nya dikosongkan agar data rapi.
        $magang = strtolower((string) $user->role) === 'magang';
        $data = [
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'nim' => $magang ? ($validated['nim'] ?? null) : $user->nim,
            'university' => $magang ? ($validated['university'] ?? null) : $user->university,
            'study_program' => $magang ? ($validated['study_program'] ?? null) : $user->study_program,
            'nip' => $magang ? $user->nip : ($validated['nip'] ?? null),
            'position' => $magang ? $user->position : ($validated['position'] ?? null),
        ];

        $user->forceFill($data)->save();

        Log::info('DARIN ubah data akun oleh admin', [
            'pelaku_id' => $request->user()->id,
            'target_id' => $user->id,
        ]);

        return back()->with('success', "Data akun {$user->name} berhasil diperbarui.");
    }

    /**
     * Nonaktifkan / aktifkan kembali akun.
     */
    public function toggleActive(Request $request, User $user)
    {
        if ($tolak = $this->tolakJikaTidakBerhak($request, $user)) {
            return $tolak;
        }

        $aktifkan = !$user->isActive();

        $user->forceFill([
            'is_active' => $aktifkan,
            // Nonaktif: keluarkan juga perangkat yang memakai "Ingat saya".
            'remember_token' => $aktifkan ? $user->remember_token : Str::random(60),
        ])->save();

        Log::info('DARIN status akun diubah oleh admin', [
            'pelaku_id' => $request->user()->id,
            'target_id' => $user->id,
            'aktif' => $aktifkan,
        ]);

        return back()->with(
            'success',
            $aktifkan
                ? "Akun {$user->name} diaktifkan kembali dan bisa login."
                : "Akun {$user->name} dinonaktifkan. Akun ini tidak bisa login sampai diaktifkan kembali."
        );
    }

    private function tolakJikaTidakBerhak(Request $request, User $user): ?RedirectResponse
    {
        $pelaku = $request->user();

        if ($pelaku->is($user)) {
            return back()->with('error', 'Untuk mengubah akun Anda sendiri, gunakan halaman Profil.');
        }

        $targetPengelola = in_array(strtolower((string) $user->role), ['admin', 'hr'], true);

        if ($targetPengelola && strtolower((string) $pelaku->role) !== 'admin') {
            return back()->with('error', 'Hanya Admin yang dapat mengubah akun Admin/HR.');
        }

        return null;
    }
}
