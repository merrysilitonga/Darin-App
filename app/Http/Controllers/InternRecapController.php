<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InternRecapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Rekap kehadiran per peserta (halaman + PDF) untuk Admin/HR, Mentor, dan peserta sendiri.
 */
class InternRecapController extends Controller
{
    public function __construct(private InternRecapService $recapService)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN / HR
    |--------------------------------------------------------------------------
    */

    public function adminShow(User $user)
    {
        $this->ensureIntern($user);

        return $this->render($user, route('admin.interns.recap-pdf', $user), route('admin.users'));
    }

    public function adminPdf(User $user)
    {
        $this->ensureIntern($user);

        return $this->pdf($user);
    }

    /*
    |--------------------------------------------------------------------------
    | MENTOR (hanya mahasiswa bimbingannya)
    |--------------------------------------------------------------------------
    */

    public function mentorShow(Request $request, User $user)
    {
        $this->ensureMentee($request, $user);

        return $this->render($user, route('mentor.interns.recap-pdf', $user), route('mentor.interns'));
    }

    public function mentorPdf(Request $request, User $user)
    {
        $this->ensureMentee($request, $user);

        return $this->pdf($user);
    }

    /*
    |--------------------------------------------------------------------------
    | PESERTA MAGANG (rekap milik sendiri)
    |--------------------------------------------------------------------------
    */

    public function mine(Request $request)
    {
        return $this->render($request->user(), route('rekap.saya.pdf'), null);
    }

    public function minePdf(Request $request)
    {
        return $this->pdf($request->user());
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    private function render(User $intern, string $pdfUrl, ?string $backUrl)
    {
        $recap = $this->recapService->build($intern);

        return view('recap.show', array_merge($recap, [
            'pdfUrl' => $pdfUrl,
            'backUrl' => $backUrl,
        ]));
    }

    private function pdf(User $intern)
    {
        $recap = $this->recapService->build($intern);

        $fileName = 'rekap-magang-' . (Str::slug($intern->name) ?: 'peserta') . '.pdf';

        return Pdf::loadView('recap.pdf', $recap)
            ->setPaper('a4', 'portrait')
            ->download($fileName);
    }

    private function ensureIntern(User $user): void
    {
        abort_unless(strtolower((string) $user->role) === 'magang', 404);
    }

    private function ensureMentee(Request $request, User $user): void
    {
        $this->ensureIntern($user);

        abort_unless((int) $user->mentor_id === (int) $request->user()->id, 403);
    }
}
