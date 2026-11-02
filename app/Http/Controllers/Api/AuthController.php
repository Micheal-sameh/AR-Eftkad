<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AvarewaseAccountNotFoundException;
use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use Avarewase\SsoClient\Client\AvarewaseClient;
use Avarewase\SsoClient\Contracts\ProvisionsAvarewaseUsers;
use Avarewase\SsoClient\Exceptions\AvarewaseConnectionException;
use Avarewase\SsoClient\Exceptions\AvarewaseTokenException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * Start "Login with Avarewase" for the web SPA (resources/views/auth/login.blade.php):
     * returns the authorize URL for the browser to open. PKCE verifier is
     * cached server-side, keyed by a one-time state value, so this stays
     * stateless from the client's point of view (no session cookie needed).
     *
     * The mobile app does not use this endpoint — it does PKCE directly
     * against avarewase-sso and sends the resulting SSO access token straight
     * to the API, validated by App\Http\Middleware\AuthenticateWithSsoOrSanctum.
     */
    public function avarewaseRedirect(AvarewaseClient $client)
    {
        $state = $client->pkce()->state();
        $verifier = $client->pkce()->verifier();
        $challenge = $client->pkce()->challengeFor($verifier);

        Cache::put($this->avarewaseCacheKey($state), $verifier, now()->addMinutes(5));

        return $this->apiResponse([
            'url' => $client->authorizationUrl($state, $challenge),
        ]);
    }

    /**
     * Complete "Login with Avarewase" for the web SPA. Accepts the `code`/
     * `state` pair either as the raw browser redirect from the SSO (GET) or
     * as a direct call from a client that intercepted the redirect itself
     * (POST) — same response shape as the normal login() either way.
     *
     * Creates the local user on first login (or updates the matching one)
     * via AvarewaseUserProvisioner; see its docblock for the matching and
     * creation rules. Issues eftkad's own Sanctum token, which the SPA
     * stores and sends as the bearer for subsequent API calls.
     */
    public function avarewaseCallback(Request $request, AvarewaseClient $client, ProvisionsAvarewaseUsers $provisioner)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        $verifier = Cache::pull($this->avarewaseCacheKey($request->string('state')->toString()));

        if (! $verifier) {
            return $this->apiErrorResponse(message: trans('messages.avarewase login failed'), status_code: 422);
        }

        try {
            $tokens = $client->exchangeCodeForTokens($request->string('code')->toString(), $verifier);
            $userInfo = $client->userInfo($tokens->accessToken);
            $user = $provisioner->resolve($userInfo);
        } catch (AvarewaseAccountNotFoundException $e) {
            return $this->apiErrorResponse(message: trans('messages.avarewase no matching account'), status_code: 404);
        } catch (AvarewaseConnectionException $e) {
            return $this->apiErrorResponse(message: trans('messages.avarewase sso unavailable'), status_code: 503);
        } catch (AvarewaseTokenException $e) {
            return $this->apiErrorResponse(message: trans('messages.avarewase login failed'), status_code: 422);
        }

        $token = $this->generateToken($user, $request);

        return $this->apiResponse([
            'token' => $token,
            'user' => new UserResource($user),
        ], trans('messages.login successfuly'));
    }

    /**
     * Local-development sign-in with membership code + password. The route is
     * only registered when config('app.dev_login') is on (never in production);
     * see routes/api.php. Same response shape as the SSO callback.
     */
    public function devLogin(Request $request)
    {
        $data = $request->validate([
            'membership_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::whereRaw('LOWER(membership_code) = ?', [mb_strtolower(trim($data['membership_code']))])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->apiErrorResponse(message: trans('messages.invalid credentials'), status_code: 422);
        }

        return $this->apiResponse([
            'token' => $this->generateToken($user, $request),
            'user' => new UserResource($user),
        ], trans('messages.login successfuly'));
    }

    private function avarewaseCacheKey(string $state): string
    {
        return "avarewase_sso:verifier:{$state}";
    }

    public function logout(Request $request)
    {
        // Null-safe: if the request has no resolvable token (already revoked, or the user was set
        // by another guard), signing out must still succeed instead of crashing with a 500.
        $request->user()?->currentAccessToken()?->delete();
        auth()->guard('web')->logout();

        return $this->apiResponse(message: trans('messages.logout successfuly'));
    }

    /**
     * No revocation here - every login just adds a new token, so other
     * open tabs/devices are never logged out. Accumulated tokens are
     * pruned weekly instead; see PruneStaleTokens.
     */
    private function generateToken($user, Request $request)
    {
        $deviceName = $this->deviceName($request);

        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Identifies the requesting device for the token's name (shown in any
     * "active sessions" listing). Clients may send a stable `device_id`
     * (e.g. persisted in local storage); otherwise falls back to a hash
     * of user agent + IP.
     */
    private function deviceName(Request $request): string
    {
        $deviceId = $request->string('device_id')->toString()
            ?: sha1($request->userAgent() . '|' . $request->ip());

        return config('app.name') . ':' . $deviceId;
    }
}
