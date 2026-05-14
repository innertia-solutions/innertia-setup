<?php

namespace App\Apps\Backoffice\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordController extends \Innertia\Auth\Http\Controllers\PasswordController
{
    // Override change(), set() here if needed

    /**
     * Enviar email de recuperación de contraseña.
     * Siempre responde 200 para no revelar si el email existe.
     */
    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Si el correo existe, recibirás las instrucciones en breve.']);
    }

    /**
     * Restablecer contraseña usando el token del email.
     */
    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $status = Password::reset(
            $data,
            function ($user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña restablecida correctamente.']);
        }

        return response()->json(['message' => __($status)], 422);
    }
}
