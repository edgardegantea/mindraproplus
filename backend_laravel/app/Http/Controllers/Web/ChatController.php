<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InferenceRequest;
use App\Services\InferenceService;

class ChatController extends Controller
{
    public function __construct(protected InferenceService $inferenceService) {}

    public function index()
    {
        $features = auth()->user()->features();
        return view('chat.index', compact('features'));
    }

    public function send(InferenceRequest $request)
    {
        $user     = $request->user();
        $features = $user->features();

        $canEmociones = $features['emociones'] ?? false;
        $canImagen    = $features['imagen']    ?? false;

        try {
            $result = $this->inferenceService->predict(
                user:            $user,
                audio:           $request->file('audio'),
                text:            $request->input('texto', ''),
                image:           $canImagen ? $request->file('image') : null,
                durationSeconds: $request->input('duration_seconds')
                    ? (float) $request->input('duration_seconds')
                    : null,
                facialEmotion:   $canImagen ? $request->input('facial_emotion')    : null,
                facialConfidence: ($canImagen && $request->input('facial_confidence'))
                    ? (float) $request->input('facial_confidence')
                    : null,
            );
        } catch (\Exception $e) {
            $result = ['ok' => false];
        }

        if (!$result['ok']) {
            $result = $this->inferenceService->publicFallback($request->input('texto', ''));
        }

        return response()->json([
            'ok'                    => true,
            'texto'                 => $result['texto'],
            // Solo devolver datos de ansiedad/emoción si el plan lo permite
            'etiqueta'              => $canEmociones ? ($result['etiqueta']             ?? null) : null,
            'probabilidad_ansiedad' => $canEmociones ? ($result['probabilidad_ansiedad'] ?? null) : null,
            'bot_response'          => $result['bot_response'],
            'emotion_label'         => ($canEmociones && $canImagen) ? ($result['emotion_label']        ?? null) : null,
            'emotion_probability'   => ($canEmociones && $canImagen) ? ($result['emotion_probability']  ?? null) : null,
        ]);
    }
}
