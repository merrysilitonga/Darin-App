<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'clock_in_start',
        'clock_in_end',
        'friday_clock_in_end',
        'clock_out_time',
        'friday_clock_out_time',
    ];

    /**
     * Mengambil satu baris pengaturan sistem, membuat nilai default bila belum ada.
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
