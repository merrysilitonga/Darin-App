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
     * Login dan mengarahkan pengguna sesuai role.
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

        // Admin dan HR.
        if (in_array($user->role, ['admin', 'hr'], true)) {
            return redirect()->intended(
                route('admin.dashboard')
            );
        }

        // Mentor.
        if ($user->role === 'mentor') {
            return redirect()->intended(
                route('mentor.dashboard')
            );
        }

        // Mahasiswa magang, termasuk akun lama dengan role intern.
        if (in_array($user->role, ['magang', 'intern'], true)) {
            return redirect()->intended(
                route('dashboard.magang')
            );
        }

        // Logout jika role tidak dikenal.
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
     * Menampilkan halaman registrasi mahasiswa.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Registrasi publik khusus mahasiswa magang.
     *
     * Role ditentukan oleh server, bukan oleh input formulir.
     * Registrasi publik tidak dapat membuat akun Admin atau mentor.
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
            // Simpan foto profil apabila pengguna mengunggahnya.
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')
                    ->store('profiles', 'public');

                if (!$photoPath) {
                    return back()
                        ->withInput($request->except([
                            'password',
                            'password_confirmation',
                        ]))
                        ->withErrors([
                            'photo' => 'Foto profil gagal disimpan. Silakan coba kembali.',
                        ]);
                }
            }

            // Role selalu ditentukan oleh aplikasi.
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'magang',

                'nim' => $validated['nim'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'university' => $validated['university'] ?? null,
                'study_program' => $validated['study_program'] ?? null,

                'division_id' => null,
                'mentor_id' => null,
                'nip' => null,
                'position' => null,

                'start_date' => null,
                'end_date' => null,

                'photo' => $photoPath,
            ]);
        } catch (Throwable $e) {
            // Hapus file foto apabila proses pembuatan akun gagal.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        // Login otomatis hanya untuk mahasiswa yang baru mendaftar.
        $user = User::where('email', $validated['email'])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.magang');
    }

    /**
     * Menampilkan form pendaftaran mentor.
     *
     * Hanya Admin dan HR yang diperbolehkan.
     */
    public function showMentorRegister()
    {
        $this->authorizeAdminOrHr();

        return view('auth.mentor-register');
    }

    /**
     * Membuat akun mentor melalui Admin/HR.
     *
     * Sesi Admin/HR tetap aktif setelah akun mentor dibuat.
     */
    public function processMentorRegister(Request $request)
    {
        $this->authorizeAdminOrHr();

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

            'nip' => [
                'nullable',
                'string',
                'max:50',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'position' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'mentor',

            'nip' => $validated['nip'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,

            'division_id' => null,
            'mentor_id' => null,
            'start_date' => null,
            'end_date' => null,
        ]);

        // Jangan login sebagai mentor yang baru dibuat.
        // Admin/HR tetap menggunakan sesi yang sedang aktif.
        return redirect()
            ->route('admin.users')
            ->with('success', 'Akun mentor berhasil dibuat.');
    }

    /**
     * Memastikan pengguna yang mengelola pendaftaran mentor
     * merupakan Admin atau HR yang sudah login.
     */
    private function authorizeAdminOrHr(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && in_array($user->role, ['admin', 'hr'], true),
            403,
            'Hanya Admin atau HR yang dapat mengelola akun mentor.'
        );
    }
}