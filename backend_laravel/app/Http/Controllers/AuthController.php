<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /** Máximo de tokens Sanctum activos por usuario (mobile). */
    private const MAX_TOKENS = 5;

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        // Email de bienvenida en cola (no bloqueante)
        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
        } catch (\Throwable $e) {
            Log::warning('WelcomeMail falló', [
                'user'  => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok'    => true,
            'user'  => $user->toApiArray(),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Credenciales inválidas.',
            ], 401);
        }

        // Limpiar tokens viejos: conservar solo los últimos MAX_TOKENS - 1
        // antes de crear el nuevo, así el total nunca supera MAX_TOKENS.
        $tokenCount = $user->tokens()->where('name', 'mobile')->count();
        if ($tokenCount >= self::MAX_TOKENS) {
            $user->tokens()
                ->where('name', 'mobile')
                ->oldest()
                ->limit($tokenCount - self::MAX_TOKENS + 1)
                ->delete();
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok'    => true,
            'user'  => $user->toApiArray(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'ok'   => true,
            'user' => array_merge($user->toApiArray(), [
                // Datos adicionales útiles para la app sin exponer campos internos
                'plan'     => $user->activePlan()?->only(['slug', 'name', 'features']),
                'features' => $user->features(),
            ]),
        ]);
    }
}
