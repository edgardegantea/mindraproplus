<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audio' => 'nullable|file|mimes:mp3,wav,m4a,aac,ogg,mp4,webm|mimetypes:audio/mpeg,audio/wav,audio/x-wav,audio/mp4,audio/aac,audio/ogg,audio/webm,video/webm|max:10240',
            'texto' => 'nullable|string|max:5000',
            'duration_seconds' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif,webp|max:10240',
            'facial_emotion' => 'nullable|string|max:50',
            'facial_confidence' => 'nullable|numeric|min:0|max:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $texto = trim((string) $this->input('texto', ''));
            $hasAudio = $this->hasFile('audio');
            $hasImage = $this->hasFile('image');

            if ($texto === '' && ! $hasAudio && ! $hasImage) {
                $validator->errors()->add('texto', 'Debes enviar al menos texto, audio o imagen.');
            }
        });
    }
}
