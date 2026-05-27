<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TherapistShare extends Model
{
    protected $fillable = ['user_id', 'token', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Genera (o renueva) el token de acceso para el terapeuta de un usuario.
     * Elimina cualquier token previo del mismo usuario.
     */
    public static function generate(int $userId, int $days = 7): self
    {
        static::where('user_id', $userId)->delete();

        return static::create([
            'user_id'    => $userId,
            'token'      => Str::random(48),
            'expires_at' => now()->addDays($days),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
