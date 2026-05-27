<?php

namespace App\Http\Controllers;

use App\Models\ProgramEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /** Catálogo de programas estructurados (datos estáticos). */
    private const PROGRAMS = [
        'anxiety-14' => [
            'slug'        => 'anxiety-14',
            'title'       => 'Manejo de ansiedad',
            'subtitle'    => 'Programa de 14 días basado en TCC',
            'description' => 'Un programa estructurado con técnicas cognitivo-conductuales, mindfulness y herramientas de regulación emocional respaldadas por evidencia científica.',
            'total_days'  => 14,
            'color'       => '#4f46e5',
            'emoji'       => '🧠',
            'days' => [
                ['day' => 1,  'title' => 'Entendiendo la ansiedad',    'type' => 'info',      'duration' => 10],
                ['day' => 2,  'title' => 'Respiración 4-7-8',          'type' => 'breathing', 'duration' => 8],
                ['day' => 3,  'title' => 'Escaneo corporal',           'type' => 'body_scan', 'duration' => 12],
                ['day' => 4,  'title' => 'Pensamientos automáticos',   'type' => 'tcc',       'duration' => 15],
                ['day' => 5,  'title' => 'Técnica 5-4-3-2-1',         'type' => 'grounding', 'duration' => 10],
                ['day' => 6,  'title' => 'Relajación muscular',        'type' => 'pmr',       'duration' => 15],
                ['day' => 7,  'title' => 'Revisión semana 1',          'type' => 'journal',   'duration' => 10],
                ['day' => 8,  'title' => 'Mindfulness básico',         'type' => 'mindful',   'duration' => 12],
                ['day' => 9,  'title' => 'Reestructuración cognitiva', 'type' => 'tcc',       'duration' => 15],
                ['day' => 10, 'title' => 'Respiración en caja',        'type' => 'breathing', 'duration' => 8],
                ['day' => 11, 'title' => 'Exposición gradual',         'type' => 'info',      'duration' => 20],
                ['day' => 12, 'title' => 'Comunicación asertiva',      'type' => 'info',      'duration' => 15],
                ['day' => 13, 'title' => 'Plan de acción personal',    'type' => 'journal',   'duration' => 20],
                ['day' => 14, 'title' => 'Celebración y cierre',       'type' => 'review',    'duration' => 15],
            ],
        ],
        'sleep-7' => [
            'slug'        => 'sleep-7',
            'title'       => 'Sueño reparador',
            'subtitle'    => 'Programa de 7 días para dormir mejor',
            'description' => 'Técnicas de higiene del sueño, relajación nocturna y manejo de pensamientos rumiativos para mejorar la calidad del descanso.',
            'total_days'  => 7,
            'color'       => '#7c3aed',
            'emoji'       => '🌙',
            'days' => [
                ['day' => 1, 'title' => 'Higiene del sueño',         'type' => 'info',      'duration' => 8],
                ['day' => 2, 'title' => 'Relajación progresiva',     'type' => 'pmr',       'duration' => 15],
                ['day' => 3, 'title' => 'Visualización guiada',      'type' => 'visual',    'duration' => 12],
                ['day' => 4, 'title' => 'Control de preocupaciones', 'type' => 'tcc',       'duration' => 10],
                ['day' => 5, 'title' => 'Restricción del sueño',     'type' => 'info',      'duration' => 8],
                ['day' => 6, 'title' => 'Rituales nocturnos',        'type' => 'journal',   'duration' => 10],
                ['day' => 7, 'title' => 'Mi protocolo personal',     'type' => 'review',    'duration' => 15],
            ],
        ],
        'stress-10' => [
            'slug'        => 'stress-10',
            'title'       => 'Control del estrés',
            'subtitle'    => 'Programa de 10 días',
            'description' => 'Herramientas prácticas para identificar, gestionar y reducir el estrés cotidiano usando técnicas de psicología positiva y regulación emocional.',
            'total_days'  => 10,
            'color'       => '#0891b2',
            'emoji'       => '⚡',
            'days' => [
                ['day' => 1,  'title' => 'Mapa de estresores',       'type' => 'journal',   'duration' => 10],
                ['day' => 2,  'title' => 'Respuesta al estrés',      'type' => 'breathing', 'duration' => 8],
                ['day' => 3,  'title' => 'Gestión del tiempo',       'type' => 'info',      'duration' => 15],
                ['day' => 4,  'title' => 'Límites saludables',       'type' => 'info',      'duration' => 15],
                ['day' => 5,  'title' => 'Mindfulness en acción',    'type' => 'mindful',   'duration' => 12],
                ['day' => 6,  'title' => 'Autocuidado activo',       'type' => 'journal',   'duration' => 10],
                ['day' => 7,  'title' => 'Pensamiento flexible',     'type' => 'tcc',       'duration' => 15],
                ['day' => 8,  'title' => 'Red de apoyo',             'type' => 'info',      'duration' => 10],
                ['day' => 9,  'title' => 'Recuperación activa',      'type' => 'pmr',       'duration' => 15],
                ['day' => 10, 'title' => 'Plan de mantenimiento',    'type' => 'review',    'duration' => 15],
            ],
        ],
    ];

    /** Lista programas con estado de inscripción del usuario. */
    public function index(Request $request): JsonResponse
    {
        $enrollments = $request->user()
            ->programEnrollments()
            ->get()
            ->keyBy('program_slug');

        $programs = array_map(function ($program) use ($enrollments) {
            $e = $enrollments->get($program['slug']);
            return array_merge($program, [
                'enrolled'       => $e !== null,
                'current_day'    => $e?->current_day ?? 0,
                'progress'       => $e?->progressPercent() ?? 0,
                'completed_days' => $e?->completed_days ?? [],
                'started_at'     => $e?->started_at?->toIso8601String(),
                'completed_at'   => $e?->completed_at?->toIso8601String(),
            ]);
        }, array_values(self::PROGRAMS));

        return response()->json(['programs' => $programs]);
    }

    /** Inscribe al usuario en un programa (o reinicia si ya existe). */
    public function enroll(Request $request, string $slug): JsonResponse
    {
        if (!isset(self::PROGRAMS[$slug])) {
            return response()->json(['message' => 'Programa no encontrado'], 404);
        }

        $program    = self::PROGRAMS[$slug];
        $enrollment = $request->user()->programEnrollments()->updateOrCreate(
            ['program_slug' => $slug],
            [
                'total_days'     => $program['total_days'],
                'current_day'    => 0,
                'completed_days' => [],
                'started_at'     => now(),
                'completed_at'   => null,
            ]
        );

        return response()->json(['enrollment' => $enrollment], 201);
    }

    /** Marca un día como completado y actualiza el progreso. */
    public function completeDay(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate(['day' => 'required|integer|min:1']);

        $enrollment = $request->user()
            ->programEnrollments()
            ->where('program_slug', $slug)
            ->firstOrFail();

        $completed = $enrollment->completed_days ?? [];
        if (!in_array($data['day'], $completed, true)) {
            $completed[] = $data['day'];
        }

        $maxDay    = max($completed);
        $isComplete = count($completed) >= $enrollment->total_days;

        $enrollment->update([
            'completed_days' => $completed,
            'current_day'    => min($maxDay, $enrollment->total_days),
            'completed_at'   => $isComplete ? now() : null,
        ]);

        return response()->json([
            'enrollment' => $enrollment->fresh(),
            'finished'   => $isComplete,
        ]);
    }
}
