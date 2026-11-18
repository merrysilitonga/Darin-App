<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Reset password pengguna oleh Admin/HR.
 * Dipakai saat pengguna lupa password dan email reset belum aktif.
 */
class AdminUserPasswordController extends Controller
{
    public function reset(Request $request, User $user)
    {
        $pelaku = $request->user();

        // Password sendiri diganti lewat halaman Profil.
        if ($pelaku->is($user)) {
            return back()->with('error', 'Untuk mengganti password akun Anda sendiri, gunakan halaman Profil.');
        }

        // HR tidak boleh mereset password akun Admin/HR lain.
        $targetPengelola = in_array(strtolower((string) $user->role), ['admin', 'hr'], true);

        if ($targetPengelola && strtolower((string) $pelaku->role) !== 'admin') {
            return back()->with('error', 'Hanya Admin yang dapat mereset password akun Admin/HR.');
        }

        $passwordBaru = $this->buatPassword();

        $user->forceFill([
            'password' => Hash::make($passwordBaru),
            // Paksa keluar dari perangkat yang memakai "Ingat saya".
            'remember_token' => Str::random(60),
        ])->save();

        Log::info('DARIN reset password oleh admin', [
            'pelaku_id' => $pelaku->id,
            'target_id' => $user->id,
        ]);

        return back()
            ->with('success', "Password {$user->name} berhasil direset.")
            ->with('password_reset', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $passwordBaru,
            ]);
    }

    /**
     * Password acak 10 karakter tanpa huruf yang mirip (0/O, 1/l).
     */
    private function buatPassword(): string
    {
        $karakter = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $hasil = '';

        for ($i = 0; $i < 10; $i++) {
            $hasil .= $karakter[random_int(0, strlen($karakter) - 1)];
        }

        return $hasil;
    }
}
