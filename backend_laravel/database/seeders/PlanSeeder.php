<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // ── Free ─────────────────────────────────────────────────────────
            [
                'name'        => 'Free',
                'slug'        => Plan::FREE,
                'description' => 'Chat de texto y voz sin costo. Ideal para empezar.',
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
            ],

            // ── Pro ───────────────────────────────────────────────────────────
            [
                'name'        => 'Pro',
                'slug'        => Plan::PRO,
                'description' => 'Análisis de emociones e historial de sesiones.',
                'price_cents' => 14900,       // $149 MXN / mes
                'currency'    => 'MXN',
                'features'    => [
                    'texto'        => true,
                    'audio'        => true,
                    'emociones'    => true,
                    'historial'    => true,
                    'imagen'       => false,
                    'estadisticas' => false,
                ],
                'trial_days'  => 7,
            ],

            // ── Plus ──────────────────────────────────────────────────────────
            [
                'name'        => 'Plus',
                'slug'        => Plan::PLUS,
                'description' => 'Acceso completo: emociones, historial, análisis facial y estadísticas.',
                'price_cents' => 19900,       // $199 MXN / mes
                'currency'    => 'MXN',
                'features'    => [
                    'texto'        => true,
                    'audio'        => true,
                    'emociones'    => true,
                    'historial'    => true,
                    'imagen'       => true,
                    'estadisticas' => true,
                ],
                'trial_days'  => 14,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        // Eliminar el plan 'full' obsoleto si existiera en BD.
        Plan::where('slug', 'full')->delete();

    }
}
