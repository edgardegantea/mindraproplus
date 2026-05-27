<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\InferenceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * PATCH /api/auth/profile
     * Actualiza nombre y/o contraseña del usuario autenticado.
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = [];

        if ($request->filled('name')) {
            $data['name'] = trim($request->input('name'));
        }

        if ($request->filled('new_password')) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'La contraseña actual es incorrecta.',
                ], 422);
            }
            $data['password'] = Hash::make($request->input('new_password'));
        }

        if (empty($data)) {
            return response()->json([
                'ok'    => false,
                'error' => 'No se enviaron cambios.',
            ], 422);
        }

        $user->update($data);

        // Si cambió la contraseña → invalidar otras sesiones (no la actual)
        if (isset($data['password'])) {
            $currentId = $user->currentAccessToken()->id;
            $user->tokens()->where('id', '!=', $currentId)->delete();
        }

        return response()->json([
            'ok'   => true,
            'user' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * DELETE /api/auth/account
     *
     * Elimina la cuenta del usuario respetando la LGPD/GDPR:
     *  - Los registros de inferencia se anonimizan (se preservan para investigación).
     *  - Datos personales (diario, evaluaciones, tokens) se borran.
     *  - El registro de usuario se anonimiza (no se borra para preservar integridad referencial).
     */
    public function deleteAccount(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Contraseña incorrecta.',
            ], 403);
        }

        // 1. Anonimizar inferencias (datos de investigación valiosos)
        InferenceRecord::where('user_id', $user->id)->update([
            'user_id'        => null,
            'input_text'     => '[eliminado]',
            'generated_text' => '[eliminado]',
        ]);

        // 2. Eliminar datos personales
        $user->moodJournals()->delete();
        $user->assessments()->delete();
        $user->therapistShares()->delete();
        $user->programEnrollments()->delete();
        $user->subscriptions()->delete();
        $user->tokens()->delete();

        // 3. Anonimizar el registro de usuario
        $stamp = now()->timestamp;
        $user->forceFill([
            'name'              => 'Usuario eliminado',
            'email'             => "deleted_{$user->id}_{$stamp}@mindra.invalid",
            'password'          => Hash::make(Str::random(40)),
            'email_verified_at' => null,
        ])->saveQuietly();

        return response()->json([
            'ok'      => true,
            'message' => 'Tu cuenta y datos personales han sido eliminados correctamente.',
        ]);
    }

    /**
     * GET /api/auth/my-data
     *
     * Exporta todos los datos del usuario como JSON estructurado.
     * Cumple con el artículo 22 de la LGPD (derecho de acceso y portabilidad).
     */
    public function myData(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'ok'          => true,
            'exported_at' => now()->toIso8601String(),
            'user'        => $user->toApiArray(),
            'journal'     => $user->moodJournals()
                ->orderByDesc('created_at')
                ->get(['created_at', 'mood_score', 'mood_label', 'note', 'tags']),
            'assessments' => $user->assessments()
                ->orderByDesc('created_at')
                ->get(['type', 'score', 'severity', 'answers', 'created_at']),
            'inference_records' => $user->inferenceRecords()
                ->orderByDesc('created_at')
                ->get([
                    'created_at', 'input_text', 'predicted_label',
                    'predicted_probability', 'emotion_label', 'model_name',
                    'audio_duration_seconds', 'transcription_source',
                ]),
            'programs' => $user->programEnrollments()
                ->get(['program_slug', 'current_day', 'total_days',
                       'completed_days', 'started_at', 'completed_at']),
        ]);
    }
}
