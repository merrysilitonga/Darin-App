<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui create() dan update().
     */
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'work_location',
        'status',
        'notes',
        'photo_path',
        'latitude',
        'longitude',
    ];

    /**
     * Konversi tipe data.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Relasi absensi dengan mahasiswa pemiliknya.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}