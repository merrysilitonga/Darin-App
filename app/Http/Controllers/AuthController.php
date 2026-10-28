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
     * Login dan mengarahkan pengguna berdasarkan role.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

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

        $role = strtolower(trim((string) $user->role));

        if (in_array($role, ['admin', 'hr'], true)) {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'mentor') {
            return redirect()->route('mentor.dashboard');
        }

        if ($role === 'magang') {
            return redirect()->route('dashboard.magang');
        }

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
     * Menampilkan formulir pendaftaran.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Registrasi mahasiswa magang, mentor, dan admin.
     *
     * Tidak menggunakan kode undangan.
     */
    public function processRegister(Request $request)
    {
        $validated = $request->validate([
            'role' => [
                'required',
                'string',
                'in:magang,mentor,admin',
            ],
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
            'nip' => [
                'nullable',
                'string',
                'max:50',
            ],
            'position' => [
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
        ], [
            'role.required' => 'Pilih jenis akun yang ingin didaftarkan.',
            'role.in' => 'Jenis akun yang dipilih tidak valid.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'photo.image' => 'Foto profil harus berupa gambar yang valid.',
            'photo.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $role = $validated['role'];
        $photoPath = null;

        try {
            /*
             * Foto profil hanya disimpan untuk mahasiswa magang.
             */
            if (
                $role === 'magang' &&
                $request->hasFile('photo')
            ) {
                $photoPath = $request->file('photo')->store(
                    'profiles',
                    'public'
                );

                if (!$photoPath) {
                    return back()
                        ->withInput($request->except([
                            'password',
                            'password_confirmation',
                            'photo',
                        ]))
                        ->withErrors([
                            'photo' => 'Foto profil gagal disimpan. Silakan coba kembali.',
                        ]);
                }
            }

            /*
             * Membuat akun berdasarkan role yang dipilih.
             */
            $user = new User();

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->role = $role;

            /*
             * Data khusus mahasiswa magang.
             */
            $user->nim = $role === 'magang'
                ? ($validated['nim'] ?? null)
                : null;

            $user->university = $role === 'magang'
                ? ($validated['university'] ?? null)
                : null;

            $user->study_program = $role === 'magang'
                ? ($validated['study_program'] ?? null)
                : null;

            $user->photo = $role === 'magang'
                ? $photoPath
                : null;

            /*
             * Data pegawai untuk mentor dan admin.
             */
            $isEmployee = in_array(
                $role,
                ['mentor', 'admin'],
                true
            );

            $user->nip = $isEmployee
                ? ($validated['nip'] ?? null)
                : null;

            $user->position = $isEmployee
                ? ($validated['position'] ?? null)
                : null;

            $user->phone = $validated['phone'] ?? null;

            /*
             * Penempatan dan periode magang dikelola melalui Admin/HR.
             */
            $user->division_id = null;
            $user->mentor_id = null;
            $user->start_date = null;
            $user->end_date = null;

            $user->save();
        } catch (Throwable $e) {
            /*
             * Hapus foto apabila pembuatan akun gagal.
             */
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        /*
         * Login otomatis setelah registrasi berhasil.
         */
        Auth::login($user);

        $request->session()->regenerate();

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'mentor') {
            return redirect()->route('mentor.dashboard');
        }

        return redirect()->route('dashboard.magang');
    }

    /**
     * Menampilkan formulir pendaftaran mentor.
     * Hanya Admin/HR yang boleh mengaksesnya.
     */
    public function showMentorRegister()
    {
        $this->authorizeAdminOrHr();

        return view('auth.mentor-register');
    }

    /**
     * Membuat akun mentor melalui Admin/HR.
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

        /*
         * Role mentor ditetapkan oleh server.
         */
        $mentor = new User();

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

        return redirect()
            ->route('admin.users')
            ->with('success', 'Akun Mentor berhasil dibuat.');
    }

    /**
     * Memastikan hanya Admin/HR yang dapat mengelola akun mentor.
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
