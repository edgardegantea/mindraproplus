<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\InferenceRequest;
use App\Services\InferenceService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected InferenceService $inferenceService) {}

    public function index()
    {
        $features = auth()->user()->features();
        return view('chat.index', compact('features'));
    }

    /**
     * POST /chat/transcribe
     * Transcribe el audio enviado y devuelve el texto.
     * El usuario puede editarlo antes de enviarlo como inferencia.
     */
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a,aac,ogg,mp4,webm|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/mp4,audio/aac,audio/ogg,audio/webm,video/webm|max:20480',
        ]);

        $result = $this->inferenceService->transcribe(
            $request->file('audio'),
            'es'
        );

        if (!$result['ok']) {
            return response()->json([
                'ok'    => false,
                'error' => $result['error'] ?? 'No se pudo transcribir el audio.',
            ], 503);
        }

        return response()->json([
            'ok'       => true,
            'text'     => $result['text'],
            'language' => $result['language'] ?? 'es',
        ]);
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
