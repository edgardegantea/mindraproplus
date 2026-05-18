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
        return view('chat.index');
    }

    public function send(InferenceRequest $request)
    {
        $result = $this->inferenceService->predict(
            user: $request->user(),
            audio: $request->file('audio'),
            text: $request->input('texto', ''),
            image: $request->file('image'),
            durationSeconds: $request->input('duration_seconds')
                ? (float) $request->input('duration_seconds')
                : null,
            facialEmotion: $request->input('facial_emotion'),
            facialConfidence: $request->input('facial_confidence')
                ? (float) $request->input('facial_confidence')
                : null,
        );

        if (!$result['ok']) {
            return response()->json(['ok' => false, 'error' => $result['error']], 400);
        }

        return response()->json([
            'ok'                   => true,
            'texto'                => $result['texto'],
            'etiqueta'             => $result['etiqueta'],
            'probabilidad_ansiedad'=> $result['probabilidad_ansiedad'],
            'bot_response'         => $result['bot_response'],
            'emotion_label'        => $result['emotion_label'] ?? null,
            'emotion_probability'  => $result['emotion_probability'] ?? null,
        ]);
    }
}
