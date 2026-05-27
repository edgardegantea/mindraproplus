<?php

namespace App\Http\Controllers;

use App\Models\TherapistShare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TherapistShareController extends Controller
{
    /**
     * Genera (o renueva) un enlace de solo lectura para el terapeuta.
     * Expira en 7 días.
     */
    public function generate(Request $request): JsonResponse
    {
        $share = TherapistShare::generate($request->user()->id, 7);
        $url   = url('/shared/' . $share->token);

        return response()->json([
            'url'        => $url,
            'expires_at' => $share->expires_at->toIso8601String(),
            'expires_in_days' => 7,
        ]);
    }
}
