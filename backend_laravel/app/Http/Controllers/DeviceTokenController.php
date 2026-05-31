<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * POST /api/auth/device-token
     * Registra o actualiza un token de dispositivo para el usuario autenticado.
     *
     * Body: { token: string, platform?: android|ios|web }
     */
    public function register(Request $request)
    {
        $request->validate([
            'token'    => 'required|string|max:500',
            'platform' => 'sometimes|in:android,ios,web',
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id'  => $request->user()->id,
                'platform' => $request->platform ?? 'android',
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /api/auth/device-token
     * Desregistra un token de dispositivo del usuario autenticado.
     *
     * Body: { token: string }
     */
    public function unregister(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
        ]);

        DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $request->token)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
