<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'features' => 'array',
        'trial_days' => 'integer',
    ];

    public const FREE = 'free';
    public const PRO  = 'pro';
    public const PLUS = 'plus';

    public static function free(): self
    {
        return self::firstOrCreate(
            ['slug' => self::FREE],
            [
                'name'        => 'Free',
                'description' => 'Acceso básico con texto y audio.',
                'price_cents' => 0,
                'currency'    => 'MXN',
                'features'    => [
                    'texto'        => true,
                    'audio'        => true,
                    'emociones'    => false,
                    'historial'    => false,
                    'imagen'       => false,
                    'estadisticas' => false,
                ],
                'trial_days'  => 0,
            ]
        );
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
