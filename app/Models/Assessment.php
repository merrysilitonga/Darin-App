<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $fillable = [
        'user_id',
        'mentor_id',
        'kedisiplinan',
        'kinerja_teknis',
        'sikap_etika',
        'laporan',
        'tanggal_penilaian',
    ];

    protected function casts(): array
    {
        return [
            'kedisiplinan' => 'float',
            'kinerja_teknis' => 'float',
            'sikap_etika' => 'float',
            'laporan' => 'float',
            'tanggal_penilaian' => 'datetime',
        ];
    }

    /** Bobot tiap aspek penilaian (total 100%). */
    public const BOBOT = [
        'kedisiplinan' => 0.25,
        'kinerja_teknis' => 0.35,
        'sikap_etika' => 0.20,
        'laporan' => 0.20,
    ];

    /**
     * Nilai akhir berbobot (0–100).
     */
    public function nilaiAkhir(): float
    {
        $total = 0;

        foreach (self::BOBOT as $aspek => $bobot) {
            $total += (float) $this->{$aspek} * $bobot;
        }

        return round($total, 2);
    }

    /**
     * Huruf mutu, sama dengan aturan di halaman Penilaian Magang.
     */
    public function grade(): string
    {
        $nilai = $this->nilaiAkhir();

        return match (true) {
            $nilai >= 85 => 'A',
            $nilai >= 75 => 'B',
            $nilai >= 60 => 'C',
            $nilai > 0 => 'D',
            default => '-',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}
