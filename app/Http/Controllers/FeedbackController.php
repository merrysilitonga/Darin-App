<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Saran & masukan dari peserta magang.
 */
class FeedbackController extends Controller
{
    /**
     * Form kirim saran + riwayat saran milik peserta.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $feedbacks = Feedback::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('feedback.index', compact('user', 'feedbacks'));
    }

    /**
     * Menyimpan saran baru.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'category' => [
                'required',
                Rule::in(Feedback::CATEGORIES),
            ],
            'subject' => [
                'required',
                'string',
                'max:150',
            ],
            'message' => [
                'required',
                'string',
                'min:10',
                'max:3000',
            ],
        ], [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'subject.required' => 'Judul wajib diisi.',
            'subject.max' => 'Judul maksimal 150 karakter.',
            'message.required' => 'Isi saran wajib diisi.',
            'message.min' => 'Isi saran minimal 10 karakter.',
            'message.max' => 'Isi saran maksimal 3.000 karakter.',
        ]);

        // Batasi jumlah kiriman per hari agar tidak spam.
        $hariIni = Carbon::today('Asia/Jakarta');

        $jumlahHariIni = Feedback::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $hariIni->copy()->timezone(config('app.timezone')))
            ->count();

        if ($jumlahHariIni >= Feedback::DAILY_LIMIT) {
            return back()
                ->withInput()
                ->with('error', 'Batas pengiriman saran hari ini (' . Feedback::DAILY_LIMIT . ' kali) sudah tercapai. Silakan coba lagi besok.');
        }

        Feedback::create([
            'user_id' => $user->id,
            'category' => $validated['category'],
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'status' => Feedback::STATUS_NEW,
        ]);

        return redirect()
            ->route('saran')
            ->with('success', 'Terima kasih! Saran Anda sudah terkirim ke Admin/HR.');
    }
}
