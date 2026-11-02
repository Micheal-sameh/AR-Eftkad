<?php

namespace App\Http\Middleware;

use Avarewase\SsoClient\Http\Middleware\AuthenticateWithSso as SsoMiddleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * eftkad's API accepts two kinds of bearer token: a Sanctum token issued by
 * its own web login flow (AuthController::avarewaseCallback, used by
 * resources/views/auth/*.blade.php), or a raw avarewase-sso access token
 * (used by the combined kenisty/eftkad/atraf mobile app, which does PKCE
 * directly against avarewase-sso and never calls eftkad's own callback).
 *
 * Tries Sanctum first since that's a cheap local DB lookup; only falls
 * through to a network call against avarewase-sso's /api/userinfo (via the
 * package's AuthenticateWithSso) when the token isn't a Sanctum token at all.
 */
class AuthenticateWithSsoOrSanctum
{
    public function __construct(protected SsoMiddleware $ssoMiddleware)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $accessToken = $token ? PersonalAccessToken::findToken($token) : null;

        if ($accessToken) {
            // findToken() doesn't enforce expiry, so check it here like Sanctum's own guard does.
            $expiration = config('sanctum.expiration');
            $expired = ($expiration && $accessToken->created_at->lte(now()->subMinutes($expiration)))
                || ($accessToken->expires_at && $accessToken->expires_at->isPast());

            $user = $expired ? null : $accessToken->tokenable;

            if (! $user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);

            return $next($request);
        }

        return $this->ssoMiddleware->handle($request, $next);
    }
}
