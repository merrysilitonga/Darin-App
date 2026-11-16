<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    /** Jenis hari libur yang diizinkan. */
    public const TYPES = [
        'Nasional',
        'Khusus ESDM',
    ];

    protected $table = 'holidays';

    protected $fillable = [
        'date',
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Hanya hari libur yang diberlakukan oleh sistem.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Hari libur aktif pada tanggal tertentu (format Y-m-d), atau null.
     */
    public static function activeOn(string $date): ?self
    {
        return static::query()
            ->active()
            ->whereDate('date', $date)
            ->first();
    }

    /**
     * Hari libur aktif setelah tanggal tertentu dalam rentang N hari ke depan.
     */
    public static function upcoming(Carbon $from, int $days = 30, int $limit = 5): Collection
    {
        return static::query()
            ->active()
            ->whereDate('date', '>', $from->toDateString())
            ->whereDate('date', '<=', $from->copy()->addDays($days)->toDateString())
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }
}
