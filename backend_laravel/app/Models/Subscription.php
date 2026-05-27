<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Subscription extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at'        => 'datetime',
        'expires_at'        => 'datetime',
        'features_override' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Devuelve las features efectivas: override si existe, o las del plan por defecto.
     */
    public function effectiveFeatures(): array
    {
        if (!empty($this->features_override)) {
            return $this->features_override;
        }

        return $this->plan?->features ?? [];
    }

    /**
     * Comprueba si una feature está activa, respetando el override.
     */
    public function hasFeature(string $key): bool
    {
        return ($this->effectiveFeatures()[$key] ?? false) === true;
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_admin_id');
    }
}
