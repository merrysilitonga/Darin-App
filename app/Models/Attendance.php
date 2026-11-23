<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;

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
        'location_id',
        'location_name',
        'distance_meters',
        'clock_out_location_id',
        'clock_out_location_name',
        'clock_out_photo_path',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_out_distance_meters',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'clock_out_latitude' => 'decimal:7',
            'clock_out_longitude' => 'decimal:7',
            'distance_meters' => 'integer',
            'clock_out_distance_meters' => 'integer',
        ];
    }

    /**
     * Relasi absensi dengan pemilik akun.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi absensi dengan lokasi saat masuk.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Relasi absensi dengan lokasi saat pulang.
     */
    public function clockOutLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'clock_out_location_id');
    }

    /** Pengajuan koreksi absen pulang terbaru untuk absensi ini. */
    public function koreksiPulang(): HasOne
    {
        return $this->hasOne(AttendanceCorrection::class)->latestOfMany();
    }

    /**
     * Keterangan saat absen pulang kosong:
     * hari ini = masih bisa absen ("Belum"), tanggal lewat = terlewat ("Tidak").
     */
    public function keteranganPulangKosong(): string
    {
        $tanggal = substr((string) $this->getRawOriginal('date'), 0, 10);

        return $tanggal === now('Asia/Jakarta')->toDateString()
            ? 'Belum absen pulang'
            : 'Tidak absen pulang';
    }
}
