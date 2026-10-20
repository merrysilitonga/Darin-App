<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login dan arahkan pengguna sesuai role.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Email atau kata sandi yang Anda masukkan salah.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Mendukung akun lama dengan role hr.
        if (in_array($user->role, ['admin', 'hr'], true)) {
            return redirect()->intended(
                route('admin.dashboard')
            );
        }

        if ($user->role === 'mentor') {
            return redirect()->intended(
                route('mentor.dashboard')
            );
        }

        if (in_array($user->role, ['magang', 'intern'], true)) {
            return redirect()->intended(
                route('dashboard.magang')
            );
        }

        // Tolak akun dengan role yang tidak dikenal.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Role akun tidak valid. Silakan hubungi administrator.',
            ]);
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Registrasi publik khusus mahasiswa magang.
     *
     * Role tidak diambil dari input pengguna.
     * Mentor dan admin harus dibuat melalui proses
     * pengelolaan akun yang memiliki otorisasi.
     */
    public function processRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'nim' => [
                'nullable',
                'string',
                'max:50',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'university' => [
                'nullable',
                'string',
                'max:255',
            ],

            'study_program' => [
                'nullable',
                'string',
                'max:255',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $photoPath = null;

        try {
            // Upload foto profil jika tersedia.
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')
                    ->store('profiles', 'public');
            }

            // Role ditetapkan server, bukan dari formulir.
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'magang',

                'nim' => $validated['nim'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'university' => $validated['university'] ?? null,
                'study_program' => $validated['study_program'] ?? null,

                'division_id' => null,
                'nip' => null,
                'position' => null,

                'start_date' => null,
                'end_date' => null,

                'photo' => $photoPath,
            ]);
        } catch (Throwable $e) {
            // Bersihkan foto jika proses penyimpanan gagal.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.magang');
    }
    /**
     * Menampilkan halaman form registrasi mentor
     */
    public function showMentorRegister()
    {
        return view('auth.mentor-register');
    }

    /**
     * Memproses data pendaftaran mentor
     */
    public function processMentorRegister(Request $request)
    {
        // 1. Validasi input dari form mentor
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'nip'      => 'nullable|string|max:50',
            'phone'    => 'nullable|string|max:30',
            'position' => 'nullable|string|max:255',
        ]);

        // 2. Simpan data mentor ke database
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role'     => 'mentor',
            'nip'      => $validated['nip'] ?? null,
            'phone'    => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        // 3. Langsung login-kan mentor setelah berhasil mendaftar
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        // 4. Arahkan ke dashboard mentor
        return redirect()->route('mentor.dashboard');
    }
}
