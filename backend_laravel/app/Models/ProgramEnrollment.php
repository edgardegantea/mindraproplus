<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEnrollment extends Model
{
    protected $fillable = [
        'user_id', 'program_slug', 'current_day',
        'total_days', 'completed_days', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'completed_days' => 'array',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function progressPercent(): int
    {
        if ($this->total_days === 0) return 0;
        return (int) round(count($this->completed_days ?? []) / $this->total_days * 100);
    }
}
