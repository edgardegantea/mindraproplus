<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TherapistShare;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TherapistShareViewController extends Controller
{
    public function show(string $token, Request $request)
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

        // ── PDF export ────────────────────────────────────────────────────────
        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('shared.therapist_pdf', compact(
                'user', 'share', 'journal', 'history', 'assessment'
            ))
            ->setPaper('letter', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', false);

            $filename = 'mindra-reporte-' . str_replace(' ', '_', strtolower($user->name))
                      . '-' . now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        }

        return view('shared.therapist', compact(
            'user', 'share', 'journal', 'history', 'assessment'
        ));
    }
}
