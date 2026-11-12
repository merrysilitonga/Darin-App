<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pengelolaan saran & masukan peserta oleh Admin/HR.
 */
class AdminFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(Feedback::STATUSES)],
            'category' => ['nullable', Rule::in(Feedback::CATEGORIES)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $validated['status'] ?? '';
        $category = $validated['category'] ?? '';
        $search = trim($validated['q'] ?? '');

        $query = Feedback::query()->with(['user', 'repliedBy']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Statistik keseluruhan (tidak terpengaruh filter).
        $statusCounts = Feedback::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            Feedback::STATUS_NEW => (int) ($statusCounts[Feedback::STATUS_NEW] ?? 0),
            Feedback::STATUS_PROCESS => (int) ($statusCounts[Feedback::STATUS_PROCESS] ?? 0),
            Feedback::STATUS_DONE => (int) ($statusCounts[Feedback::STATUS_DONE] ?? 0),
        ];

        // Saran baru tampil paling atas, lalu terbaru.
        $feedbacks = $query
            ->orderByRaw('CASE WHEN status = ? THEN 0 WHEN status = ? THEN 1 ELSE 2 END', [
                Feedback::STATUS_NEW,
                Feedback::STATUS_PROCESS,
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.feedbacks', compact(
            'feedbacks',
            'stats',
            'status',
            'category',
            'search'
        ));
    }

    /**
     * Ubah status dan/atau isi tanggapan.
     */
    public function update(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Feedback::STATUSES)],
            'admin_reply' => ['nullable', 'string', 'max:3000'],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'admin_reply.max' => 'Tanggapan maksimal 3.000 karakter.',
        ]);

        $reply = trim($validated['admin_reply'] ?? '');

        $data = [
            'status' => $validated['status'],
            'admin_reply' => $reply !== '' ? $reply : null,
        ];

        // Catat penanggap hanya jika isi tanggapan berubah.
        if ($reply !== '' && $reply !== (string) $feedback->admin_reply) {
            $data['replied_by'] = $request->user()->id;
            $data['replied_at'] = Carbon::now();
        }

        if ($reply === '') {
            $data['replied_by'] = null;
            $data['replied_at'] = null;
        }

        $feedback->update($data);

        return back()->with('success', 'Saran "' . $feedback->subject . '" berhasil diperbarui.');
    }
}
