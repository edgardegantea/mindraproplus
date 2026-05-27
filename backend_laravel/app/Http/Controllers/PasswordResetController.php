<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * POST /api/auth/forgot-password
     *
     * Genera un token seguro de 64 chars y envía el correo con el enlace.
     * SIEMPRE responde 200 para prevenir enumeración de correos.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            $token = Str::random(64);

            // Almacenar (reemplaza cualquier token previo del mismo email)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            try {
                Mail::to($user->email)->queue(new PasswordResetMail($user, $token));
            } catch (\Throwable $e) {
                Log::warning('PasswordResetMail falló', [
                    'user'  => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Respuesta genérica — no revela si el email existe o no
        return response()->json([
            'ok'      => true,
            'message' => 'Si el correo está registrado, recibirás las instrucciones en breve.',
        ]);
    }

    /**
     * POST /api/auth/reset-password
     *
     * Verifica el token (válido 60 min) y actualiza la contraseña.
     * Invalida todos los tokens Sanctum para forzar re-login.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|max:255',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (!$record || !Hash::check($request->input('token'), $record->token)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Token inválido o expirado.',
            ], 422);
        }

        // Verificar expiración (60 minutos)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->input('email'))
                ->delete();

            return response()->json([
                'ok'    => false,
                'error' => 'El enlace ha expirado. Solicita uno nuevo.',
            ], 422);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'ok'    => false,
                'error' => 'Usuario no encontrado.',
            ], 404);
        }

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();

        // Invalidar sesiones activas (forzar re-login tras cambio de contraseña)
        $user->tokens()->delete();

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Contraseña actualizada correctamente. Inicia sesión de nuevo.',
        ]);
    }
}
