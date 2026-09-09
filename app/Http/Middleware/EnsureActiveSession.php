<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $sessionToken = $request->session()->get('active_session_token');

        if ($user->active_session_token === null) {
            $sessionToken = Str::random(64);
            $tokenHash = hash('sha256', $sessionToken);
            $claimed = $user->newQuery()
                ->whereKey($user->id)
                ->whereNull('active_session_token')
                ->update([
                    'active_session_token' => $tokenHash,
                    'active_session_last_seen_at' => now(),
                ]);

            if ($claimed === 1) {
                $request->session()->put(
                    'active_session_token',
                    $sessionToken,
                );
                $user->forceFill([
                    'active_session_token' => $tokenHash,
                    'active_session_last_seen_at' => now(),
                ]);
            } else {
                $user->refresh();
            }
        }

        if (
            ! is_string($sessionToken)
            || ! hash_equals(
                (string) $user->active_session_token,
                hash('sha256', $sessionToken),
            )
        ) {
            return $this->replacedSessionResponse($request);
        }

        if (
            $user->active_session_last_seen_at === null
            || $user->active_session_last_seen_at->lt(now()->subSeconds(30))
        ) {
            $user->forceFill([
                'active_session_last_seen_at' => now(),
            ])->save();
        }

        return $next($request);
    }

    private function replacedSessionResponse(
        Request $request,
    ): JsonResponse|RedirectResponse {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Esta sesión se cerró porque la cuenta se abrió en otro lugar.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'session_replaced' => true,
                'redirect_url' => route('login', [
                    'sesion' => 'reemplazada',
                ]),
            ], 401);
        }

        return redirect()->route('login', [
            'sesion' => 'reemplazada',
        ]);
    }
}
