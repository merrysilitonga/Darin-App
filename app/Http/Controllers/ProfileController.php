<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil pengguna.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.index', compact('user'));
    }

    /**
     * Menyimpan perubahan profil pengguna.
     *
     * Periode magang tidak boleh diubah melalui halaman profil.
     * Perubahan periode hanya dilakukan melalui fitur Admin/HR.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Hanya field yang diizinkan yang divalidasi.
        // start_date dan end_date sengaja tidak disertakan.
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
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'nim' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'nim')->ignore($user->id),
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
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',

            'nim.unique' => 'NIM sudah digunakan akun lain.',

            'photo.image' => 'Foto profil harus berupa gambar.',
            'photo.mimes' =>
                'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        // Pisahkan data profil dari file foto.
        // Hanya field profil yang tervalidasi yang boleh diisi.
        $profileData = collect($validated)
            ->except('photo')
            ->all();

        $fotoLama = $user->photo;
        $fotoBaru = null;

        // Simpan foto baru jika pengguna mengunggahnya.
        if ($request->hasFile('photo')) {
            $fotoBaru = $request->file('photo')
                ->store('profiles', 'public');

            if (!$fotoBaru) {
                return back()
                    ->with(
                        'error',
                        'Foto profil gagal disimpan. Silakan coba kembali.'
                    )
                    ->withInput(
                        $request->except([
                            'photo',
                            'password',
                            'password_confirmation',
                        ])
                    );
            }
        }

        try {
            // Perbarui hanya data profil yang diizinkan.
            $user->fill($profileData);

            // Foto diperbarui hanya jika ada unggahan baru.
            if ($fotoBaru !== null) {
                $user->photo = $fotoBaru;
            }

            // start_date, end_date, role, dan field lain
            // yang tidak diizinkan tidak diubah oleh metode ini.
            $user->save();
        } catch (Throwable $e) {
            // Hapus foto baru jika penyimpanan database gagal.
            if ($fotoBaru !== null) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        // Hapus foto lama setelah data profil berhasil disimpan.
        if (
            $fotoBaru !== null &&
            $fotoLama &&
            $fotoLama !== $fotoBaru
        ) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()
            ->route('profil')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
