<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use Illuminate\Http\Request;

class ProgramWebController extends Controller
{
    private const PROGRAMS = [
        'anxiety-14' => [
            'slug'        => 'anxiety-14',
            'title'       => 'Manejo de ansiedad',
            'subtitle'    => 'Programa de 14 días basado en TCC',
            'description' => 'Técnicas cognitivo-conductuales, mindfulness y herramientas de regulación emocional respaldadas por evidencia científica.',
            'total_days'  => 14,
            'color'       => '#4f46e5',
            'emoji'       => '🧠',
            'days' => [
                ['day'=>1,  'title'=>'Entendiendo la ansiedad',    'type'=>'info',      'duration'=>10, 'content'=>'La ansiedad es una respuesta natural del cuerpo ante situaciones percibidas como amenazantes. Hoy aprenderás a identificar tus patrones de ansiedad y comprender por qué ocurren.'],
                ['day'=>2,  'title'=>'Respiración 4-7-8',          'type'=>'breathing', 'duration'=>8,  'content'=>'Inhala por la nariz durante 4 segundos → Sostén la respiración 7 segundos → Exhala lentamente por la boca durante 8 segundos. Repite 4 veces.'],
                ['day'=>3,  'title'=>'Escaneo corporal',           'type'=>'body_scan', 'duration'=>12, 'content'=>'Cierra los ojos y lleva tu atención desde la coronilla hasta los pies, notando sensaciones sin juzgarlas. Identifica dónde guardas la tensión.'],
                ['day'=>4,  'title'=>'Pensamientos automáticos',   'type'=>'tcc',       'duration'=>15, 'content'=>'Los pensamientos automáticos surgen sin que los invitemos. Hoy aprenderás a identificarlos y cuestionarlos: ¿Hay evidencia real que los soporte?'],
                ['day'=>5,  'title'=>'Técnica 5-4-3-2-1',         'type'=>'grounding', 'duration'=>10, 'content'=>'Grounding: nombra 5 cosas que ves, 4 que puedes tocar, 3 que escuchas, 2 que hueles, 1 que puedes saborear. Ancla tu mente al presente.'],
                ['day'=>6,  'title'=>'Relajación muscular',        'type'=>'pmr',       'duration'=>15, 'content'=>'Relaja progresivamente cada grupo muscular: tensa 5 segundos y libera. Empieza por los pies, sube hasta la cara.'],
                ['day'=>7,  'title'=>'Revisión semana 1',          'type'=>'journal',   'duration'=>10, 'content'=>'Escribe en tu diario: ¿Qué técnicas funcionaron mejor? ¿En qué momentos sentiste más alivio? ¿Qué quieres practicar esta semana?'],
                ['day'=>8,  'title'=>'Mindfulness básico',         'type'=>'mindful',   'duration'=>12, 'content'=>'Siéntate cómodamente, cierra los ojos y observa tus pensamientos como nubes que pasan. No los sigas, solo obsérvalos pasar.'],
                ['day'=>9,  'title'=>'Reestructuración cognitiva', 'type'=>'tcc',       'duration'=>15, 'content'=>'Identifica un pensamiento negativo recurrente. Busca evidencia a favor y en contra. Crea un pensamiento alternativo más balanceado.'],
                ['day'=>10, 'title'=>'Respiración en caja',        'type'=>'breathing', 'duration'=>8,  'content'=>'Box breathing: Inhala 4s → Sostén 4s → Exhala 4s → Sostén 4s. Repite 6 ciclos. Ideal para momentos de estrés agudo.'],
                ['day'=>11, 'title'=>'Exposición gradual',         'type'=>'info',      'duration'=>20, 'content'=>'La evitación mantiene la ansiedad. La exposición gradual consiste en enfrentar lo que temes de forma progresiva, de menor a mayor intensidad.'],
                ['day'=>12, 'title'=>'Comunicación asertiva',      'type'=>'info',      'duration'=>15, 'content'=>'Comunicarte asertivamente reduce el estrés interpersonal. Aprende a expresar tus necesidades con claridad y respeto usando el "Yo siento/necesito/pido".'],
                ['day'=>13, 'title'=>'Plan de acción personal',    'type'=>'journal',   'duration'=>20, 'content'=>'Diseña tu plan de mantenimiento: ¿Qué técnicas usarás diariamente? ¿Cuáles en momentos de crisis? ¿Cuándo buscarás apoyo profesional?'],
                ['day'=>14, 'title'=>'Celebración y cierre',       'type'=>'review',    'duration'=>15, 'content'=>'¡Completaste el programa! Reflexiona sobre tu progreso: ¿Qué aprendiste sobre ti mismo/a? ¿Cómo te sientes comparado con el día 1?'],
            ],
        ],
        'sleep-7' => [
            'slug'        => 'sleep-7',
            'title'       => 'Sueño reparador',
            'subtitle'    => 'Programa de 7 días para dormir mejor',
            'description' => 'Técnicas de higiene del sueño, relajación nocturna y manejo de pensamientos rumiativos.',
            'total_days'  => 7,
            'color'       => '#7c3aed',
            'emoji'       => '🌙',
            'days' => [
                ['day'=>1, 'title'=>'Higiene del sueño',         'type'=>'info',    'duration'=>8,  'content'=>'Reglas básicas: horario fijo, habitación oscura y fresca, sin pantallas 1h antes, evitar cafeína después de las 3pm.'],
                ['day'=>2, 'title'=>'Relajación progresiva',     'type'=>'pmr',     'duration'=>15, 'content'=>'Practica la relajación muscular progresiva específica para dormir: empieza relajado en cama, tensa y libera cada grupo muscular.'],
                ['day'=>3, 'title'=>'Visualización guiada',      'type'=>'visual',  'duration'=>12, 'content'=>'Imagina un lugar seguro y tranquilo. Visualiza cada detalle: colores, sonidos, temperatura. Deja que tu cuerpo se hunda en la cama.'],
                ['day'=>4, 'title'=>'Control de preocupaciones', 'type'=>'tcc',     'duration'=>10, 'content'=>'Escribe tus preocupaciones antes de acostarte. Cuando aparezcan en la cama, recuerda: "Ya las anoté, mañana las atenderé."'],
                ['day'=>5, 'title'=>'Restricción del sueño',     'type'=>'info',    'duration'=>8,  'content'=>'Técnica avanzada: limita el tiempo en cama a tu promedio real de sueño para consolidar el descanso. Solo entra a la cama cuando tengas sueño.'],
                ['day'=>6, 'title'=>'Rituales nocturnos',        'type'=>'journal', 'duration'=>10, 'content'=>'Diseña tu rutina de 30 minutos antes de dormir: 3 cosas por las que estás agradecido/a, lectura relajante, herbal, preparar lo del día siguiente.'],
                ['day'=>7, 'title'=>'Mi protocolo personal',     'type'=>'review',  'duration'=>15, 'content'=>'Crea tu protocolo de sueño personalizado combinando las técnicas que mejor funcionaron para ti esta semana.'],
            ],
        ],
        'stress-10' => [
            'slug'        => 'stress-10',
            'title'       => 'Control del estrés',
            'subtitle'    => 'Programa de 10 días',
            'description' => 'Herramientas prácticas para identificar, gestionar y reducir el estrés cotidiano.',
            'total_days'  => 10,
            'color'       => '#0891b2',
            'emoji'       => '⚡',
            'days' => [
                ['day'=>1,  'title'=>'Mapa de estresores',       'type'=>'journal',   'duration'=>10, 'content'=>'Dibuja tu "mapa de estrés": lista tus principales estresores, califica su intensidad del 1-10 y clasifícalos: ¿Son controlables? ¿Urgentes? ¿Importantes?'],
                ['day'=>2,  'title'=>'Respuesta al estrés',      'type'=>'breathing', 'duration'=>8,  'content'=>'Cuando sientas estrés activado: Para → Respira (4-7-8) → Observa tus pensamientos → Decide cómo responder en lugar de reaccionar.'],
                ['day'=>3,  'title'=>'Gestión del tiempo',       'type'=>'info',      'duration'=>15, 'content'=>'Matriz de Eisenhower: divide tus tareas en Urgente+Importante, No urgente+Importante, Urgente+No importante, Ni urgente ni importante.'],
                ['day'=>4,  'title'=>'Límites saludables',       'type'=>'info',      'duration'=>15, 'content'=>'Los límites no son paredes, son puertas que controlas. Practica decir "No" o "Déjame pensarlo" con respeto y firmeza.'],
                ['day'=>5,  'title'=>'Mindfulness en acción',    'type'=>'mindful',   'duration'=>12, 'content'=>'Elige una actividad cotidiana (lavar platos, caminar) y hazla con plena atención. Nota cada sensación sin distracciones.'],
                ['day'=>6,  'title'=>'Autocuidado activo',       'type'=>'journal',   'duration'=>10, 'content'=>'El autocuidado no es egoísmo, es mantenimiento. Identifica 3 actividades que te recargan y programa una para esta semana.'],
                ['day'=>7,  'title'=>'Pensamiento flexible',     'type'=>'tcc',       'duration'=>15, 'content'=>'Identifica tus "reglas rígidas" (debería, tengo que, siempre, nunca). Reformúlalas como preferencias flexibles.'],
                ['day'=>8,  'title'=>'Red de apoyo',             'type'=>'info',      'duration'=>10, 'content'=>'Mapea tu red de apoyo: ¿Quién te escucha? ¿A quién le pides ayuda práctica? ¿Con quién te ríes? Cultiva esas relaciones activamente.'],
                ['day'=>9,  'title'=>'Recuperación activa',      'type'=>'pmr',       'duration'=>15, 'content'=>'La recuperación activa es tan importante como la productividad. Practica la relajación profunda y permítete descansar sin culpa.'],
                ['day'=>10, 'title'=>'Plan de mantenimiento',    'type'=>'review',    'duration'=>15, 'content'=>'Diseña tu sistema de manejo de estrés sostenible: ¿Qué haces diariamente? ¿Qué señales de alerta detectarás? ¿Qué harás cuando el estrés suba?'],
            ],
        ],
    ];

    private static function typeIcon(string $type): string
    {
        return match($type) {
            'breathing' => '🫁',
            'tcc'       => '🧩',
            'pmr'       => '💆',
            'journal'   => '📝',
            'mindful'   => '🧘',
            'grounding' => '🌿',
            'body_scan' => '🔍',
            'visual'    => '🌅',
            'review'    => '🏆',
            default     => '📖',
        };
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = ProgramEnrollment::where('user_id', $user->id)
            ->get()
            ->keyBy('program_slug');

        $programs = collect(self::PROGRAMS)->map(function ($program) use ($enrollments) {
            $enrollment = $enrollments->get($program['slug']);
            $program['enrollment']   = $enrollment;
            $program['progress']     = $enrollment ? $enrollment->progressPercent() : 0;
            $program['completed']    = $enrollment?->isCompleted() ?? false;
            $program['current_day']  = $enrollment?->current_day ?? 0;
            $program['days_done']    = $enrollment ? count($enrollment->completed_days ?? []) : 0;
            $program['type_icon']    = fn($type) => self::typeIcon($type);
            return $program;
        });

        // Si se solicita ver un programa específico
        $active = null;
        if ($slug = $request->query('programa')) {
            $active     = self::PROGRAMS[$slug] ?? null;
            $enrollment = $enrollments->get($slug);
            if ($active) {
                $active['enrollment'] = $enrollment;
                $active['days_done']  = $enrollment ? ($enrollment->completed_days ?? []) : [];
                foreach ($active['days'] as &$day) {
                    $day['icon']      = self::typeIcon($day['type']);
                    $day['completed'] = in_array($day['day'], $active['days_done']);
                }
            }
        }

        return view('programs.index', compact('programs', 'active'));
    }

    public function enroll(Request $request, string $slug)
    {
        if (!isset(self::PROGRAMS[$slug])) abort(404);

        $program = self::PROGRAMS[$slug];

        ProgramEnrollment::updateOrCreate(
            ['user_id' => $request->user()->id, 'program_slug' => $slug],
            [
                'total_days'     => $program['total_days'],
                'current_day'    => 1,
                'completed_days' => [],
                'started_at'     => now(),
                'completed_at'   => null,
            ]
        );

        return redirect()->route('programs', ['programa' => $slug])
            ->with('success', '¡Inscripción exitosa! Empieza con el Día 1.');
    }

    public function completeDay(Request $request, string $slug, int $day)
    {
        if (!isset(self::PROGRAMS[$slug])) abort(404);

        $enrollment = ProgramEnrollment::where('user_id', $request->user()->id)
            ->where('program_slug', $slug)
            ->firstOrFail();

        $done = $enrollment->completed_days ?? [];
        if (!in_array($day, $done)) {
            $done[] = $day;
        }

        $nextDay   = max($done) + 1;
        $completed = count($done) >= $enrollment->total_days ? now() : null;

        $enrollment->update([
            'completed_days' => $done,
            'current_day'    => min($nextDay, $enrollment->total_days),
            'completed_at'   => $completed,
        ]);

        $msg = $completed
            ? '🎉 ¡Completaste el programa! Excelente trabajo.'
            : "✅ Día {$day} completado.";

        return redirect()->route('programs', ['programa' => $slug])
            ->with('success', $msg);
    }
}
