<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Persetujuan pendaftar baru (mahasiswa yang daftar sendiri lewat /register).
 * Saat disetujui, Admin/HR sekaligus menetapkan lokasi, mentor, divisi, dan periode magang.
 */
class AdminRegistrationController extends Controller
{
    public function approve(Request $request, User $user)
    {
        abort_unless($user->role === 'magang' && $user->isPendingApproval(), 404);

        $validated = $request->validate([
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('is_active', true),
            ],
            'mentor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'mentor'),
            ],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ], [
            'location_id.required' => 'Lokasi absensi wajib dipilih.',
            'location_id.exists' => 'Lokasi tidak ditemukan atau nonaktif.',
            'mentor_id.exists' => 'Mentor yang dipilih tidak valid.',
            'division_id.exists' => 'Divisi yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.date_format' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.',
        ]);

        $user->forceFill([
            'location_id' => (int) $validated['location_id'],
            'mentor_id' => $validated['mentor_id'] ?? null,
            'division_id' => $validated['division_id'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'approval_status' => 'Disetujui',
            'is_active' => true,
        ])->save();

        $lokasi = Location::find($validated['location_id'])?->name ?? '-';

        return back()->with(
            'success',
            "Pendaftaran {$user->name} disetujui (lokasi: {$lokasi}). Mahasiswa sudah dapat login."
        );
    }

    /** Tolak pendaftar: akun dan foto profilnya dihapus (belum punya data absensi). */
    public function reject(User $user)
    {
        abort_unless($user->role === 'magang' && $user->isPendingApproval(), 404);

        $nama = $user->name;
        $foto = $user->photo;

        DB::transaction(fn () => $user->delete());

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        return back()->with('success', "Pendaftaran {$nama} ditolak dan akunnya dihapus.");
    }
}
