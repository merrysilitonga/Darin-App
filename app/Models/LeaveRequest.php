<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_type',
        'start_date',
        'end_date',
        'reason',
        'document_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Mahasiswa yang mengajukan izin atau sakit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mentor yang memproses pengajuan.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    /**
     * Tanggal dalam rentang izin yang ternyata tetap absen (mis. Dinas Luar)
     * dan sah, sehingga tidak dihitung sebagai hari izin/sakit.
     *
     * @return \Illuminate\Support\Collection<int, string> tanggal Y-m-d
     */
    public function tanggalTetapAbsen()
    {
        if (!$this->start_date || !$this->end_date) {
            return collect();
        }

        return Attendance::query()
            ->where('user_id', $this->user_id)
            ->whereNotNull('clock_in')
            ->sah()
            ->whereDate('date', '>=', $this->start_date->toDateString())
            ->whereDate('date', '<=', $this->end_date->toDateString())
            ->orderBy('date')
            ->get(['date'])
            ->map(fn (Attendance $absen) => $absen->date->toDateString())
            ->values();
    }
}
