<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /**
     * Nama tabel pengaturan sistem.
     */
    protected $table = 'system_settings';

    /**
     * Kolom yang boleh diperbarui melalui mass assignment.
     */
    protected $fillable = [
        'clock_in_start',
        'clock_in_end',
        'friday_clock_in_end',
        'clock_out_time',
        'friday_clock_out_time',
    ];

    /**
     * Mengambil satu baris pengaturan sistem.
     * Jika belum ada, buat pengaturan default dengan ID 1.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'clock_in_start' => '08:00',
                'clock_in_end' => '17:00',
                'friday_clock_in_end' => '17:30',
                'clock_out_time' => '17:00',
                'friday_clock_out_time' => '17:30',
            ]
        );
    }
}
