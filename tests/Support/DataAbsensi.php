<?php

namespace Tests\Support;

use App\Models\Location;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

/**
 * Data uji bersama untuk tes absensi DARIN.
 *
 * Jadwal uji: masuk mulai 08:00, batas masuk 12:00, pulang mulai 16:00.
 * Lokasi uji: (-6.2, 106.8) dengan radius 100 meter.
 */
trait DataAbsensi
{
    /** Koordinat kantor uji. */
    protected float $kantorLat = -6.2;

    protected float $kantorLng = 106.8;

    /**
     * JPEG asli 1x1 piksel (base64), agar tes tidak bergantung pada ekstensi GD.
     */
    private const JPEG_1X1 = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDABALDA4MChAODQ4SERATGCgaGBYWGDEjJR0oOjM9PDkzODdASFxOQERXRTc4UG1RV19iZ2hnPk1xeXBkeFxlZ2P/2wBDARESEhgVGC8aGi9jQjhCY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2P/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwC7RRRXiHoH/9k=';

    /**
     * Mengatur "sekarang" ke waktu WIB tertentu.
     */
    protected function setWaktuWib(string $waktu): void
    {
        Carbon::setTestNow(Carbon::parse($waktu, 'Asia/Jakarta'));
    }

    /**
     * Menyimpan jadwal kerja uji pada konfigurasi global (ID 1).
     */
    protected function aturJadwalKerja(): void
    {
        SystemSetting::current()->fill([
            'clock_in_start' => '08:00',
            'clock_in_end' => '12:00',
            'friday_clock_in_end' => '11:00',
            'clock_out_time' => '16:00',
            'friday_clock_out_time' => '16:30',
        ])->save();
    }

    protected function buatLokasi(array $atribut = []): Location
    {
        return Location::create(array_merge([
            'name' => 'Kantor Uji',
            'address' => 'Jl. Uji No. 1',
            'latitude' => $this->kantorLat,
            'longitude' => $this->kantorLng,
            'radius_meters' => 100,
            'is_active' => true,
        ], $atribut));
    }

    /**
     * Membuat mahasiswa magang aktif (periode November–Desember 2026).
     */
    protected function buatMagang(?Location $lokasi = null, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'magang',
            'location_id' => $lokasi?->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-12-31',
        ], $atribut));
    }

    protected function fotoSelfie(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'selfie.jpg',
            base64_decode(self::JPEG_1X1)
        );
    }

    /**
     * Payload form absensi. Default: posisi tepat di titik kantor.
     */
    protected function dataAbsen(array $override = []): array
    {
        return array_merge([
            'photo' => $this->fotoSelfie(),
            'latitude' => $this->kantorLat,
            'longitude' => $this->kantorLng,
        ], $override);
    }

    /**
     * Koordinat sekitar 1,1 km dari kantor (di luar radius 100 m).
     */
    protected function koordinatLuarRadius(): array
    {
        return [
            'latitude' => $this->kantorLat - 0.01,
            'longitude' => $this->kantorLng,
        ];
    }
}