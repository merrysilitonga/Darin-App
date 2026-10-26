<?php

namespace App\Http\Controllers;

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
     * Login dan mengarahkan pengguna sesuai role akun.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Autentikasi pengguna.
        if (!Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'Email atau kata sandi yang Anda masukkan salah.',
                ])
                ->onlyInput('email');
        }

        // Regenerasi session setelah login berhasil.
        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun tidak dapat diverifikasi. Silakan coba kembali.',
                ]);
        }

        // Normalisasi role untuk menghindari masalah spasi
        // atau perbedaan huruf besar dan huruf kecil.
        $role = strtolower(trim((string) $user->role));

        // ADMIN DAN HR.
        if (in_array($role, ['admin', 'hr'], true)) {
            return redirect()->route('admin.dashboard');
        }

        // MENTOR.
        if ($role === 'mentor') {
            return redirect()->route('mentor.dashboard');
        }

        // MAHASISWA MAGANG.
        // Standar role mahasiswa hanya "magang".
        if ($role === 'magang') {
            return redirect()->route('dashboard.magang');
        }

        // Role tidak dikenal: akhiri session.
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
     */
    public function processRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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
            'nim' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'university' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $photoPath = null;

        try {
            // Simpan foto profil jika pengguna mengunggah foto.
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')
                    ->store('profiles', 'public');

                if (!$photoPath) {
                    return back()
                        ->withInput(
                            $request->except([
                                'password',
                                'password_confirmation',
                            ])
                        )
                        ->withErrors([
                            'photo' => 'Foto profil gagal disimpan. Silakan coba kembali.',
                        ]);
                }
            }

            // Role ditentukan oleh server, bukan formulir.
            $user = new \App\Models\User();

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->role = 'magang';

            $user->nim = $validated['nim'] ?? null;
            $user->phone = $validated['phone'] ?? null;
            $user->university = $validated['university'] ?? null;
            $user->study_program = $validated['study_program'] ?? null;

            $user->division_id = null;
            $user->mentor_id = null;
            $user->nip = null;
            $user->position = null;

            $user->start_date = null;
            $user->end_date = null;

            $user->photo = $photoPath;

            $user->save();
        } catch (Throwable $e) {
            // Hapus foto jika pembuatan akun gagal.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        // Mahasiswa yang baru mendaftar masuk ke dashboard mahasiswa.
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard.magang');
    }

    /**
     * Menampilkan formulir pendaftaran Mentor.
     * Hanya Admin/HR yang diperbolehkan.
     */
    public function showMentorRegister()
    {
        $this->authorizeAdminOrHr();

        return view('auth.mentor-register');
    }

    /**
     * Membuat akun Mentor melalui Admin/HR.
     */
    public function processMentorRegister(Request $request)
    {
        $this->authorizeAdminOrHr();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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
            'nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        // Role Mentor ditentukan oleh server.
        $mentor = new \App\Models\User();

        $mentor->name = $validated['name'];
        $mentor->email = $validated['email'];
        $mentor->password = Hash::make($validated['password']);
        $mentor->role = 'mentor';

        $mentor->nip = $validated['nip'] ?? null;
        $mentor->phone = $validated['phone'] ?? null;
        $mentor->position = $validated['position'] ?? null;

        $mentor->division_id = null;
        $mentor->mentor_id = null;

        $mentor->start_date = null;
        $mentor->end_date = null;

        $mentor->save();

        // Admin/HR tetap menggunakan akunnya sendiri.
        return redirect()
            ->route('admin.users')
            ->with('success', 'Akun Mentor berhasil dibuat.');
    }

    /**
     * Memastikan hanya Admin/HR yang dapat mengelola akun Mentor.
     */
    private function authorizeAdminOrHr(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && in_array(
                strtolower(trim((string) $user->role)),
                ['admin', 'hr'],
                true
            ),
            403,
            'Hanya Admin atau HR yang dapat mengelola akun Mentor.'
        );
    }
}
