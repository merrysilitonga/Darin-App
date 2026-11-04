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
     * Mengambil satu konfigurasi global DARIN.
     *
     * ID 1 digunakan sebagai identitas tetap konfigurasi.
     * Pengaturan yang sudah tersimpan tidak akan ditimpa
     * oleh nilai default.
     */
    public static function current(): self
    {
        $settings = static::query()->find(1);

        if ($settings) {
            return $settings;
        }

        // Buat konfigurasi hanya jika ID 1 belum tersedia.
        $settings = new static([
            'clock_in_start' => '08:00',
            'clock_in_end' => '17:00',
            'friday_clock_in_end' => '17:30',
            'clock_out_time' => '17:00',
            'friday_clock_out_time' => '17:30',
        ]);

        $settings->setAttribute('id', 1);
        $settings->save();

        return $settings;
    }
}
