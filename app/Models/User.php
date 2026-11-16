<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'division_id',
        'location_id',
        'mentor_id',
        'nim',
        'phone',
        'university',
        'study_program',
        'photo',
        'nip',
        'position',
        'start_date',
        'end_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /*
     * Status periode magang, diturunkan dari start_date & end_date
     * (tanpa kolom status terpisah, jadi selalu konsisten dengan periode).
     */
    public const INTERNSHIP_NOT_SET = 'belum_diatur';
    public const INTERNSHIP_UPCOMING = 'belum_mulai';
    public const INTERNSHIP_ACTIVE = 'aktif';
    public const INTERNSHIP_ENDED = 'selesai';

    /**
     * Status periode magang pada tanggal tertentu (default: hari ini WIB).
     */
    public function internshipStatus(?string $date = null): string
    {
        if (!$this->start_date || !$this->end_date) {
            return self::INTERNSHIP_NOT_SET;
        }

        $date ??= now('Asia/Jakarta')->toDateString();

        if ($date < $this->start_date->toDateString()) {
            return self::INTERNSHIP_UPCOMING;
        }

        if ($date > $this->end_date->toDateString()) {
            return self::INTERNSHIP_ENDED;
        }

        return self::INTERNSHIP_ACTIVE;
    }

    /**
     * True jika user adalah mahasiswa magang yang periodenya sudah berakhir.
     */
    public function isInternshipEnded(): bool
    {
        return $this->role === 'magang'
            && $this->internshipStatus() === self::INTERNSHIP_ENDED;
    }

    /**
     * Relasi user dengan divisi.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Relasi mahasiswa dengan lokasi magang yang ditugaskan.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Relasi mahasiswa dengan mentor yang ditugaskan.
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /**
     * Relasi mentor dengan mahasiswa yang dibimbing.
     */
    public function mentees(): HasMany
    {
        return $this->hasMany(User::class, 'mentor_id');
    }

    /**
     * Relasi user dengan absensi.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}