<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedEmail = mb_strtolower(trim((string) config(
            'security.user_management_admin_email',
        )));
        $userEmail = mb_strtolower(trim((string) $request->user()?->email));

        abort_unless(
            $allowedEmail !== '' && hash_equals($allowedEmail, $userEmail),
            403,
            'No tienes autorización para administrar usuarios.',
        );

        return $next($request);
    }
}
