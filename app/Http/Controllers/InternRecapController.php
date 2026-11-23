<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\User;
use App\Services\InternRecapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Rekap kehadiran per peserta (halaman + PDF) dan rapor nilai akhir,
 * untuk Admin/HR, Mentor, dan peserta sendiri.
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

        return $this->render($user, route('admin.interns.recap-pdf', $user), route('admin.users'), route('admin.interns.rapor', $user));
    }

    public function adminPdf(User $user)
    {
        $this->ensureIntern($user);

        return $this->pdf($user);
    }

    public function adminRapor(User $user)
    {
        $this->ensureIntern($user);

        return $this->rapor($user);
    }

    /*
    |--------------------------------------------------------------------------
    | MENTOR (hanya mahasiswa bimbingannya)
    |--------------------------------------------------------------------------
    */

    public function mentorShow(Request $request, User $user)
    {
        $this->ensureMentee($request, $user);

        return $this->render($user, route('mentor.interns.recap-pdf', $user), route('mentor.interns'), route('mentor.assessment.pdf', $user->id));
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
        return $this->render($request->user(), route('rekap.saya.pdf'), null, route('rekap.saya.rapor'));
    }

    public function minePdf(Request $request)
    {
        return $this->pdf($request->user());
    }

    public function mineRapor(Request $request)
    {
        return $this->rapor($request->user());
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    private function render(User $intern, string $pdfUrl, ?string $backUrl, ?string $raporUrl = null)
    {
        $recap = $this->recapService->build($intern);

        // Nilai akhir dari Mentor (null jika belum dinilai).
        $assessment = Assessment::with('mentor:id,name')->where('user_id', $intern->id)->first();

        return view('recap.show', array_merge($recap, [
            'pdfUrl' => $pdfUrl,
            'backUrl' => $backUrl,
            'assessment' => $assessment,
            'raporUrl' => $assessment ? $raporUrl : null,
        ]));
    }

    /**
     * Rapor nilai akhir (PDF yang sama dengan unduhan Mentor). 404 jika belum dinilai.
     */
    private function rapor(User $intern)
    {
        $assessment = Assessment::where('user_id', $intern->id)->firstOrFail();
        $student = $intern->loadMissing(['division', 'location', 'mentor']);
        $mentor = User::find($assessment->mentor_id) ?? $student->mentor;

        abort_unless($mentor, 404);

        $fileName = 'rapor-magang-' . (Str::slug($student->name) ?: 'peserta') . '.pdf';

        return Pdf::loadView('mentor.assessment-pdf', compact('mentor', 'student', 'assessment'))
            ->setPaper('a4', 'portrait')
            ->download($fileName);
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
