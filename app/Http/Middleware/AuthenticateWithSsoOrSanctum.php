<?php

namespace App\Http\Middleware;

use App\Exceptions\AvarewaseAccountNotFoundException;
use Avarewase\SsoClient\Client\AvarewaseClient;
use Avarewase\SsoClient\Exceptions\AvarewaseSsoException;
use Avarewase\SsoClient\Http\Middleware\AuthenticateWithSso as SsoMiddleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        $allowed = config('app.sso_allowed_client_ids', []);

        if (! $allowed) {
            return $this->viaSso($request, $next);
        }

        // Raw SSO tokens are only accepted when issued to one of our own clients. The
        // userinfo response is inspected before the shared middleware can provision a user.
        $raw = $token ? $this->userInfoClaims($token) : null;

        if (! $raw || ! $this->audienceAllowed($raw, $allowed)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $this->viaSso($request, $next);
    }

    /**
     * The provisioner refuses identities with no usable local account (e.g. a deleted user);
     * that is an authentication failure, not a server error.
     */
    protected function viaSso(Request $request, Closure $next): Response
    {
        try {
            return $this->ssoMiddleware->handle($request, $next);
        } catch (AvarewaseAccountNotFoundException) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    }

    protected function userInfoClaims(string $token): ?array
    {
        // Same key and TTL as the package middleware, so the lookup is shared with it.
        $cacheKey = 'avarewase-sso:userinfo:'.hash('sha256', $token);

        $raw = Cache::remember($cacheKey, config('avarewase-sso.cache_ttl', 60), function () use ($token) {
            try {
                return app(AvarewaseClient::class)->userInfo($token)->raw;
            } catch (AvarewaseSsoException) {
                return null;
            }
        });

        if ($raw === null) {
            Cache::forget($cacheKey);
        }

        return $raw;
    }

    protected function audienceAllowed(array $claims, array $allowed): bool
    {
        $values = array_merge(
            (array) ($claims['aud'] ?? []),
            (array) ($claims['azp'] ?? []),
            (array) ($claims['client_id'] ?? []),
        );

        return (bool) array_intersect($allowed, array_map('strval', $values));
    }
}
