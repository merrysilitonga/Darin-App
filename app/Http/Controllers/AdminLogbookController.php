<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Monitoring logbook seluruh peserta (lintas divisi) untuk Admin/HR.
 * Hanya membaca data; persetujuan tetap dilakukan oleh mentor.
 */
class AdminLogbookController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
        ]);

        $bulan = $validated['bulan'] ?? Carbon::now('Asia/Jakarta')->format('Y-m');

        $awal = Carbon::createFromFormat('!Y-m', $bulan, 'Asia/Jakarta')->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();

        $logbooks = Logbook::query()
            ->with(['user.division', 'user.mentor'])
            // Draft belum dikirim peserta, jadi tidak ditampilkan.
            ->where('status', '!=', 'Draft')
            ->whereDate('date', '>=', $awal->toDateString())
            ->whereDate('date', '<=', $akhir->toDateString())
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.logbooks', compact('logbooks', 'bulan'));
    }
}
