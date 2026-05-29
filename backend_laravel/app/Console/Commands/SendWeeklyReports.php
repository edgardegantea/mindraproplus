<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportMail;
use App\Models\InferenceRecord;
use App\Models\ProgramEnrollment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReports extends Command
{
    protected $signature   = 'mindra:weekly-reports {--dry-run : Solo muestra cuántos emails se enviarían}';
    protected $description = 'Envía el reporte semanal de bienestar a todos los usuarios activos';

    private const PROGRAM_NAMES = [
        'anxiety-14' => ['title' => 'Manejo de ansiedad',  'emoji' => '🧠'],
        'sleep-7'    => ['title' => 'Sueño reparador',     'emoji' => '🌙'],
        'stress-10'  => ['title' => 'Control del estrés',  'emoji' => '⚡'],
    ];

    public function handle(): int
    {
        $weekStart = now()->subDays(7)->startOfDay();
        $weekEnd   = now()->endOfDay();
        $dryRun    = $this->option('dry-run');

        $this->info("Generando reportes semanales ({$weekStart->format('d/m/Y')} – {$weekEnd->format('d/m/Y')})…");

        // Usuarios con cuenta verificada (o sin email_verified_at requerida)
        $users = User::whereNotNull('email')
            ->where('role', '!=', 'superadmin')
            ->get();

        $sent  = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $stats = $this->buildStats($user, $weekStart, $weekEnd);

            if ($dryRun) {
                $this->line("  [dry-run] {$user->email} — {$stats['sessions']} sesiones");
                $sent++;
                continue;
            }

            try {
                Mail::to($user->email)->send(new WeeklyReportMail($user, $stats));
                $sent++;
                $this->line("  ✓ {$user->email}");
            } catch (\Exception $e) {
                $skipped++;
                $this->warn("  ✗ {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Listo. Enviados: {$sent} | Errores: {$skipped}");

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function buildStats(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        // --- Sesiones de la semana ---
        $records = InferenceRecord::where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->whereNotNull('predicted_probability')
            ->orderBy('created_at')
            ->get();

        $sessions   = $records->count();
        $avgProb    = $sessions > 0 ? round($records->avg('predicted_probability'), 3) : null;
        $activeDays = $records->groupBy(fn($r) => $r->created_at->toDateString())->count();

        // --- Tendencia vs semana anterior ---
        $prevWeekStart = $weekStart->copy()->subDays(7);
        $prevRecords   = InferenceRecord::where('user_id', $user->id)
            ->whereBetween('created_at', [$prevWeekStart, $weekStart])
            ->whereNotNull('predicted_probability')
            ->get();

        $trend      = $this->detectTrend($records, $prevRecords);
        $trendColor = match($trend) {
            'improving', 'stable_low' => '#16a34a',
            'worsening', 'high'       => '#b91c1c',
            default                   => '#475569',
        };

        // --- Actividad diaria (últimos 7 días) ---
        $dailyProbs   = [];
        $missingDays  = [];
        $dayLabels    = ['Lu','Ma','Mi','Ju','Vi','Sa','Do'];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $dayLabel = $dayLabels[$day->dayOfWeekIso - 1];

            $dayRecords = $records->filter(fn($r) => $r->created_at->toDateString() === $key);

            if ($dayRecords->isNotEmpty()) {
                $dailyProbs[] = [
                    'label' => $dayLabel,
                    'prob'  => round($dayRecords->avg('predicted_probability'), 2),
                ];
            } else {
                $missingDays[] = $dayLabel;
            }
        }

        // --- Programas activos ---
        $programs = ProgramEnrollment::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->get()
            ->map(function ($e) {
                $meta = self::PROGRAM_NAMES[$e->program_slug] ?? null;
                if (!$meta) return null;
                return [
                    'title'      => $meta['title'],
                    'emoji'      => $meta['emoji'],
                    'days_done'  => count($e->completed_days ?? []),
                    'total_days' => $e->total_days,
                    'progress'   => $e->total_days > 0
                        ? round((count($e->completed_days ?? []) / $e->total_days) * 100)
                        : 0,
                ];
            })
            ->filter()
            ->values()
            ->all();

        // --- Insights ---
        $insights = $this->buildInsights($sessions, $avgProb, $activeDays, $trend);

        return [
            'week_label'  => $weekStart->format('d/m') . ' – ' . $weekEnd->format('d/m/Y'),
            'sessions'    => $sessions,
            'active_days' => $activeDays,
            'avg_prob'    => $avgProb,
            'trend'       => $trend,
            'trend_color' => $trendColor,
            'daily_probs' => $dailyProbs,
            'missing_days'=> $missingDays,
            'programs'    => $programs,
            'insights'    => $insights,
        ];
    }

    private function detectTrend($weekRecords, $prevRecords): string
    {
        if ($weekRecords->count() < 1) return 'unknown';

        $currentAvg = $weekRecords->avg('predicted_probability');

        // Si hay datos de la semana anterior, comparar
        if ($prevRecords->count() >= 1) {
            $prevAvg = $prevRecords->avg('predicted_probability');

            if ($currentAvg < $prevAvg - 0.10) return 'improving';
            if ($currentAvg > $prevAvg + 0.10) return 'worsening';
        }

        // Sin datos previos — analizar solo la semana actual
        if ($currentAvg > 0.60) return 'high';
        if ($currentAvg < 0.35) return 'stable_low';

        return 'stable';
    }

    private function buildInsights(int $sessions, ?float $avgProb, int $activeDays, string $trend): array
    {
        $insights = [];

        if ($sessions === 0) {
            $insights[] = ['color' => '#f59e0b', 'text' => 'No tuviste sesiones esta semana. Volver a Mindra puede ayudarte a mantener el seguimiento de tu bienestar.'];
            return $insights;
        }

        if ($activeDays >= 5) {
            $insights[] = ['color' => '#16a34a', 'text' => "Usaste Mindra {$activeDays} días esta semana. ¡Excelente constancia!"];
        } elseif ($activeDays >= 3) {
            $insights[] = ['color' => '#6366f1', 'text' => "Tuviste {$activeDays} días activos. Intentar llegar a 5 días puede darte mejores resultados."];
        }

        if ($sessions >= 7) {
            $insights[] = ['color' => '#6366f1', 'text' => "{$sessions} sesiones esta semana — estás muy comprometido/a con tu bienestar."];
        }

        if ($avgProb !== null) {
            $pct = round($avgProb * 100);
            if ($avgProb > 0.65) {
                $insights[] = ['color' => '#ef4444', 'text' => "Tu nivel promedio de ansiedad esta semana fue {$pct}%. Si persiste, considera hablar con un profesional."];
            } elseif ($avgProb < 0.30) {
                $insights[] = ['color' => '#16a34a', 'text' => "Nivel de ansiedad muy bajo ({$pct}%). ¡Tu trabajo está dando resultados!"];
            }
        }

        if ($trend === 'improving') {
            $insights[] = ['color' => '#16a34a', 'text' => 'Comparado con la semana anterior, tus indicadores de ansiedad bajaron. Continúa con las técnicas que te están funcionando.'];
        } elseif ($trend === 'worsening') {
            $insights[] = ['color' => '#f97316', 'text' => 'Esta semana los indicadores subieron un poco respecto a la anterior. Prueba dedicar 10 minutos a respiración consciente o el programa de manejo de ansiedad.'];
        }

        return $insights;
    }
}
