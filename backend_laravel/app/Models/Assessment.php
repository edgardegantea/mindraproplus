<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    protected $fillable = ['user_id', 'type', 'score', 'answers', 'severity'];

    protected $casts = [
        'score'   => 'integer',
        'answers' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula la severidad según el puntaje y el tipo de evaluación.
     *
     * GAD-7: 0-4 minimal · 5-9 mild · 10-14 moderate · 15-21 severe
     * PHQ-9: 0-4 minimal · 5-9 mild · 10-14 moderate · 15-19 moderately_severe · 20-27 severe
     */
    public static function severityFromScore(int $score, string $type = 'gad7'): string
    {
        return match ($type) {
            'phq9' => match (true) {
                $score <= 4  => 'minimal',
                $score <= 9  => 'mild',
                $score <= 14 => 'moderate',
                $score <= 19 => 'moderately_severe',
                default      => 'severe',
            },
            default => match (true) {             // gad7
                $score <= 4  => 'minimal',
                $score <= 9  => 'mild',
                $score <= 14 => 'moderate',
                default      => 'severe',
            },
        };
    }

    /**
     * Etiqueta legible en español.
     */
    public static function severityLabel(string $severity): string
    {
        return match ($severity) {
            'minimal'           => 'Mínima',
            'mild'              => 'Leve',
            'moderate'          => 'Moderada',
            'moderately_severe' => 'Moderada-grave',
            'severe'            => 'Grave',
            default             => 'Desconocida',
        };
    }
}
