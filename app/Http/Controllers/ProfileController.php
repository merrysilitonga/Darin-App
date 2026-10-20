<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.index', compact('user'));
    }

    /**
     * Menyimpan perubahan profil.
     */
    public function update(Request $request)
    {
        $user = $request->user();

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

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
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
            'end_date.after_or_equal' =>
                'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'photo.image' => 'Foto profil harus berupa gambar.',
            'photo.mimes' =>
                'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        // Simpan foto baru jika pengguna mengunggahnya.
        $fotoLama = $user->photo;
        $fotoBaru = null;

        if ($request->hasFile('photo')) {
            $fotoBaru = $request->file('photo')
                ->store('profiles', 'public');

            if (!$fotoBaru) {
                return back()
                    ->with('error', 'Foto profil gagal disimpan.')
                    ->withInput();
            }

            $validated['photo'] = $fotoBaru;
        }

        try {
            // Perbarui data yang telah divalidasi.
            $user->fill($validated);
            $user->save();
        } catch (\Throwable $e) {
            // Bersihkan foto baru jika database gagal menyimpan.
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        // Hapus foto lama setelah foto baru berhasil disimpan.
        if (
            $fotoBaru &&
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
 