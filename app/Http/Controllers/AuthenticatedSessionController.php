<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    private const MAX_FAILED_ATTEMPTS = 5;

    private const LOCK_MINUTES = 15;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
            'takeover' => ['sometimes', 'boolean'],
        ], [
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        $credentials = [
            'email' => mb_strtolower(trim($data['email'])),
            'password' => $data['password'],
        ];

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();
        $accountAttemptKey = $this->accountAttemptKey(
            $credentials['email'],
        );
        $attemptKeys = [
            $accountAttemptKey,
            $this->originAttemptKey($request),
        ];

        if ($user?->locked_until?->isFuture()) {
            return $this->lockedResponse();
        }

        if ($user?->locked_until?->isPast()) {
            $user->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ])->save();

            RateLimiter::clear($accountAttemptKey);
        }

        if ($this->isLocked($attemptKeys)) {
            return $this->lockedResponse();
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            foreach ($attemptKeys as $attemptKey) {
                RateLimiter::hit(
                    $attemptKey,
                    self::LOCK_MINUTES * 60,
                );
            }

            if ($user) {
                $failedAttempts = min(
                    self::MAX_FAILED_ATTEMPTS,
                    $user->failed_login_attempts + 1,
                );

                $user->forceFill([
                    'failed_login_attempts' => $failedAttempts,
                    'locked_until' => $failedAttempts
                        >= self::MAX_FAILED_ATTEMPTS
                            ? now()->addMinutes(self::LOCK_MINUTES)
                            : null,
                ])->save();
            }

            $highestAttemptCount = max(array_map(
                fn (string $attemptKey): int => RateLimiter::attempts(
                    $attemptKey,
                ),
                $attemptKeys,
            ));

            if ($highestAttemptCount >= self::MAX_FAILED_ATTEMPTS) {
                return $this->lockedResponse();
            }

            return response()->json([
                'message' => 'Correo o contraseña incorrectos.',
                'remaining_attempts' => self::MAX_FAILED_ATTEMPTS
                    - $highestAttemptCount,
            ], 422);
        }

        foreach ($attemptKeys as $attemptKey) {
            RateLimiter::clear($attemptKey);
        }

        $user?->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $sessionStarted = DB::transaction(function () use (
            $request,
            $user,
            $data,
        ): bool {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            if (
                $this->hasCompetingActiveSession($request, $lockedUser)
                && ! ($data['takeover'] ?? false)
            ) {
                return false;
            }

            Auth::login($lockedUser);
            $request->session()->regenerate();

            $sessionToken = Str::random(64);
            $request->session()->put(
                'active_session_token',
                $sessionToken,
            );
            $lockedUser->forceFill([
                'active_session_token' => hash('sha256', $sessionToken),
                'active_session_last_seen_at' => now(),
            ])->save();

            return true;
        });

        if (! $sessionStarted) {
            return response()->json([
                'message' => 'Esta cuenta ya está abierta en otro lugar. ¿Quieres abrirla aquí?',
                'requires_takeover' => true,
            ], 409);
        }

        return response()->json([
            'message' => 'Acceso correcto.',
            'redirect_url' => route('home'),
        ]);
    }

    private function accountAttemptKey(string $email): string
    {
        return 'login:account:'.hash('sha256', $email);
    }

    private function originAttemptKey(Request $request): string
    {
        return 'login:origin:'.hash(
            'sha256',
            $request->ip() ?? 'unknown',
        );
    }

    /**
     * @param  array<int, string>  $attemptKeys
     */
    private function isLocked(array $attemptKeys): bool
    {
        foreach ($attemptKeys as $attemptKey) {
            if (RateLimiter::tooManyAttempts(
                $attemptKey,
                self::MAX_FAILED_ATTEMPTS,
            )) {
                return true;
            }
        }

        return false;
    }

    private function lockedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Demasiados intentos fallidos. El acceso está bloqueado durante 15 minutos.',
            'retry_after_seconds' => self::LOCK_MINUTES * 60,
        ], 429);
    }

    private function hasCompetingActiveSession(
        Request $request,
        User $user,
    ): bool {
        if (
            $user->active_session_token === null
            || $user->active_session_last_seen_at === null
            || $user->active_session_last_seen_at->lt(
                now()->subMinutes((int) config('session.lifetime')),
            )
        ) {
            return false;
        }

        $currentToken = $request->session()->get('active_session_token');

        return ! is_string($currentToken)
            || ! hash_equals(
                $user->active_session_token,
                hash('sha256', $currentToken),
            );
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionToken = $request->session()->get('active_session_token');

        if (
            $user
            && is_string($sessionToken)
            && is_string($user->active_session_token)
            && hash_equals(
                $user->active_session_token,
                hash('sha256', $sessionToken),
            )
        ) {
            $user->forceFill([
                'active_session_token' => null,
                'active_session_last_seen_at' => null,
            ])->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
