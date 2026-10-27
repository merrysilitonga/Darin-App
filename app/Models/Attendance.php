<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function clockOutLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'clock_out_location_id');
    }
}
