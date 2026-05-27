<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'crisis_alerts',
        'weekly_summary',
        'assessment_reminders',
        'streak_reminders',
    ];

    protected $casts = [
        'crisis_alerts'        => 'boolean',
        'weekly_summary'       => 'boolean',
        'assessment_reminders' => 'boolean',
        'streak_reminders'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Devuelve las preferencias del usuario, creando los valores por defecto si no existen todavía.
     */
    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'crisis_alerts'        => true,
                'weekly_summary'       => true,
                'assessment_reminders' => false,
                'streak_reminders'     => false,
            ]
        );
    }
}
