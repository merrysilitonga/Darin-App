<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    public const CATEGORIES = [
        'Saran',
        'Kendala Aplikasi',
        'Lainnya',
    ];

    public const STATUS_NEW = 'Baru';
    public const STATUS_PROCESS = 'Diproses';
    public const STATUS_DONE = 'Selesai';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_PROCESS,
        self::STATUS_DONE,
    ];

    /** Batas kiriman per peserta per hari (mencegah spam). */
    public const DAILY_LIMIT = 5;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Kelas warna badge status (Tailwind).
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DONE => 'bg-green-50 text-green-700',
            self::STATUS_PROCESS => 'bg-blue-50 text-blue-700',
            default => 'bg-amber-50 text-amber-700',
        };
    }
}
