<?php

namespace App\Http\Controllers;

use App\Mail\PasswordRecoveryCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PasswordRecoveryController extends Controller
{
    private const CODE_LIFETIME_MINUTES = 10;

    private const MAX_CODE_ATTEMPTS = 5;

    public function sendCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ], [
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'Escribe un correo electrónico válido.',
        ]);

        $normalizedEmail = mb_strtolower(trim($data['email']));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Este correo no está registrado.',
                'registered' => false,
                'code_sent' => false,
            ], 422);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ],
        );

        try {
            Mail::to($user->email)->send(
                new PasswordRecoveryCodeMail($user, $code),
            );
        } catch (Throwable $exception) {
            report($exception);

            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();

            return response()->json([
                'message' => 'No pudimos enviar el código. Inténtalo de nuevo.',
                'registered' => true,
                'code_sent' => false,
            ], 503);
        }

        $request->session()->put([
            'password_recovery.email' => $user->email,
            'password_recovery.attempts' => 0,
        ]);

        return response()->json([
            'message' => 'Código enviado correctamente.',
            'masked_email' => $this->maskEmail($user->email),
            'registered' => true,
            'code_sent' => true,
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Escribe el código que recibiste.',
            'code.digits' => 'El código debe tener 6 números.',
        ]);

        $email = $request->session()->get('password_recovery.email');

        if (! is_string($email) || $email === '') {
            return response()->json([
                'message' => 'Solicita un código nuevo para continuar.',
            ], 422);
        }

        $attempts = (int) $request->session()->get(
            'password_recovery.attempts',
            0,
        );

        if ($attempts >= self::MAX_CODE_ATTEMPTS) {
            $this->forgetRecovery($request, $email);

            return response()->json([
                'message' => 'Superaste el número de intentos. Solicita otro código.',
            ], 429);
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where(
                'created_at',
                '>=',
                now()->subMinutes(self::CODE_LIFETIME_MINUTES),
            )
            ->first();

        if (! $reset) {
            $this->forgetRecovery($request, $email);

            return response()->json([
                'message' => 'El código venció. Solicita uno nuevo.',
            ], 422);
        }

        if (! Hash::check($data['code'], $reset->token)) {
            $attempts++;
            $request->session()->put('password_recovery.attempts', $attempts);
            $remainingAttempts = self::MAX_CODE_ATTEMPTS - $attempts;

            if ($remainingAttempts <= 0) {
                $this->forgetRecovery($request, $email);

                return response()->json([
                    'message' => 'Superaste el número de intentos. Solicita otro código.',
                ], 429);
            }

            return response()->json([
                'message' => "El código no es correcto. Te quedan {$remainingAttempts} intentos.",
            ], 422);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->forgetRecovery($request, $email);

            return response()->json([
                'message' => 'La cuenta ya no está disponible.',
            ], 422);
        }

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        $request->session()->forget('password_recovery');
        $request->session()->put([
            'password_recovery_verified.user_id' => $user->id,
            'password_recovery_verified.expires_at' => now()
                ->addMinutes(self::CODE_LIFETIME_MINUTES)
                ->timestamp,
        ]);

        return response()->json([
            'message' => 'Código verificado correctamente.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $userId = $request->session()->get(
            'password_recovery_verified.user_id',
        );
        $expiresAt = (int) $request->session()->get(
            'password_recovery_verified.expires_at',
            0,
        );

        if (! $userId || $expiresAt < now()->timestamp) {
            $request->session()->forget('password_recovery_verified');

            return response()->json([
                'message' => 'La autorización venció. Solicita un código nuevo.',
            ], 422);
        }

        $data = $request->validate([
            'password' => [
                'required',
                'string',
                'max:128',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'not_regex:/\s/',
            ],
            'password_confirmation' => ['required', 'string'],
        ], [
            'password.required' => 'Escribe la contraseña nueva.',
            'password.max' => 'La contraseña no puede superar 128 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'Incluye mayúscula, minúscula, número y símbolo.',
            'password.not_regex' => 'La contraseña no debe contener espacios.',
            'password_confirmation.required' => 'Repite la contraseña nueva.',
        ]);

        $user = User::query()->find($userId);

        if (! $user) {
            $request->session()->forget('password_recovery_verified');

            return response()->json([
                'message' => 'La cuenta ya no está disponible.',
            ], 422);
        }

        $usedPasswordHashes = DB::table('password_histories')
            ->where('user_id', $user->id)
            ->pluck('password_hash')
            ->prepend($user->password);

        foreach ($usedPasswordHashes as $passwordHash) {
            if (Hash::check($data['password'], $passwordHash)) {
                return response()->json([
                    'message' => 'Esta contraseña ya fue utilizada. Elige una diferente.',
                    'errors' => [
                        'password' => [
                            'Esta contraseña ya fue utilizada. Elige una diferente.',
                        ],
                    ],
                ], 422);
            }
        }

        DB::transaction(function () use ($user, $data): void {
            DB::table('password_histories')->insertOrIgnore([
                'user_id' => $user->id,
                'password_hash' => $user->password,
                'created_at' => now(),
            ]);

            $user->forceFill([
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(60),
                'active_session_token' => null,
                'active_session_last_seen_at' => null,
            ])->save();

            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        });

        $request->session()->forget('password_recovery_verified');

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    private function forgetRecovery(Request $request, string $email): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        $request->session()->forget('password_recovery');
    }

    private function maskEmail(string $email): string
    {
        [$localPart, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visibleLength = min(2, mb_strlen($localPart));
        $visible = mb_substr($localPart, 0, $visibleLength);
        $hiddenLength = max(3, mb_strlen($localPart) - $visibleLength);

        return $visible.str_repeat('•', $hiddenLength).'@'.$domain;
    }
}
