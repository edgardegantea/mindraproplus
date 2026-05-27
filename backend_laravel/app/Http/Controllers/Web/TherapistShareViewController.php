<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TherapistShare;

class TherapistShareViewController extends Controller
{
    public function show(string $token)
    {
        $share = TherapistShare::with('user')
            ->where('token', $token)
            ->first();

        if (!$share || $share->isExpired()) {
            abort(404, 'Este enlace ha expirado o ya no es válido.');
        }

        $user = $share->user;

        $journal = $user->moodJournals()
            ->orderByDesc('created_at')
            ->take(30)
            ->get();

        $history = $user->inferenceRecords()
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        $assessment = $user->assessments()
            ->where('type', 'gad7')
            ->latest()
            ->first();

        return view('shared.therapist', compact(
            'user', 'share', 'journal', 'history', 'assessment'
        ));
    }
}
