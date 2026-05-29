<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\Request;

class AssessmentWebController extends Controller
{
    private const GAD7_QUESTIONS = [
        'Sentirme nervioso/a, ansioso/a o con los nervios de punta',
        'No poder dejar de preocuparme o no poder controlar la preocupación',
        'Preocuparme demasiado por diferentes cosas',
        'Dificultad para relajarme',
        'Estar tan inquieto/a que es difícil permanecer sentado/a tranquilamente',
        'Molestarme o irritarme fácilmente',
        'Sentir miedo como si algo terrible fuera a pasar',
    ];

    private const PHQ9_QUESTIONS = [
        'Poco interés o placer en hacer cosas',
        'Sentirme desanimado/a, deprimido/a o sin esperanzas',
        'Problemas para dormir, mantenerse dormido/a, o dormir demasiado',
        'Sentirme cansado/a o tener poca energía',
        'Tener poco apetito o comer en exceso',
        'Sentirme mal conmigo mismo/a, o sentir que soy un fracaso',
        'Dificultad para concentrarme en cosas como leer o ver televisión',
        'Moverme o hablar tan lento que otras personas podrían notarlo, o lo contrario',
        'Pensamientos de que estaría mejor muerto/a o de hacerme daño',
    ];

    private const OPTIONS = [
        0 => 'Nunca',
        1 => 'Varios días',
        2 => 'Más de la mitad de los días',
        3 => 'Casi todos los días',
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $recent = $user->assessments()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($a) => [
                'id'            => $a->id,
                'type'          => strtoupper($a->type),
                'score'         => $a->score,
                'severity'      => $a->severity,
                'severity_label'=> Assessment::severityLabel($a->severity),
                'severity_color'=> Assessment::severityColor($a->severity),
                'created_at'    => $a->created_at->format('d/m/Y'),
                'answers'       => $a->answers,
            ]);

        return view('assessments.index', [
            'gad7Questions' => self::GAD7_QUESTIONS,
            'phq9Questions' => self::PHQ9_QUESTIONS,
            'options'       => self::OPTIONS,
            'recent'        => $recent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|in:gad7,phq9',
            'answers' => 'required|array',
            'answers.*' => 'integer|min:0|max:3',
        ]);

        $minItems = $data['type'] === 'phq9' ? 9 : 7;
        if (count($data['answers']) < $minItems) {
            return back()->withErrors(['answers' => "Debes responder las {$minItems} preguntas."]);
        }

        $score    = array_sum($data['answers']);
        $severity = Assessment::severityFromScore($score, $data['type']);

        $request->user()->assessments()->create([
            'type'     => $data['type'],
            'score'    => $score,
            'answers'  => $data['answers'],
            'severity' => $severity,
        ]);

        return redirect()->route('assessments')
            ->with('success', 'Evaluación guardada. ' . Assessment::severityAdvice($severity));
    }
}
