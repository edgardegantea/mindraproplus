<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    /** GET /api/notifications/preferences */
    public function show(Request $request): JsonResponse
    {
        $prefs = NotificationPreference::forUser($request->user()->id);

        return response()->json([
            'ok'          => true,
            'preferences' => $this->toArray($prefs),
        ]);
    }

    /** PUT /api/notifications/preferences */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'crisis_alerts'        => 'sometimes|boolean',
            'weekly_summary'       => 'sometimes|boolean',
            'assessment_reminders' => 'sometimes|boolean',
            'streak_reminders'     => 'sometimes|boolean',
        ]);

        if (empty($validated)) {
            return response()->json([
                'ok'    => false,
                'error' => 'No se enviaron preferencias para actualizar.',
            ], 422);
        }

        $prefs = NotificationPreference::forUser($request->user()->id);
        $prefs->update($validated);

        return response()->json([
            'ok'          => true,
            'preferences' => $this->toArray($prefs->fresh()),
        ]);
    }

    private function toArray(NotificationPreference $prefs): array
    {
        return [
            'crisis_alerts'        => $prefs->crisis_alerts,
            'weekly_summary'       => $prefs->weekly_summary,
            'assessment_reminders' => $prefs->assessment_reminders,
            'streak_reminders'     => $prefs->streak_reminders,
        ];
    }
}
