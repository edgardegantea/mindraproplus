<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodJournal extends Model
{
    protected $fillable = [
        'user_id',
        'mood_score',
        'mood_emoji',
        'mood_label',
        'note',
        'tags',
    ];

    protected $casts = [
        'mood_score' => 'integer',
        'tags'       => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Mapeo estático score → emoji + label. */
    public static function fromScore(int $score): array
    {
        return match ($score) {
            1 => ['emoji' => '😔', 'label' => 'Muy mal'],
            2 => ['emoji' => '😕', 'label' => 'Mal'],
            3 => ['emoji' => '😐', 'label' => 'Regular'],
            4 => ['emoji' => '🙂', 'label' => 'Bien'],
            5 => ['emoji' => '😄', 'label' => 'Excelente'],
            default => ['emoji' => '😐', 'label' => 'Regular'],
        };
    }
}
