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
use Illuminate\Support\Facades\Cache;

class AuthController extends BaseController
{
    /**
     * Start "Login with Avarewase": returns the authorize URL for the
     * client (mobile app / webview) to open. PKCE verifier is cached
     * server-side, keyed by a one-time state value, so this stays
     * stateless from the client's point of view (no session cookie needed).
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
     * Complete "Login with Avarewase". Accepts the `code`/`state` pair
     * either as the raw browser redirect from the SSO (GET) or as a
     * direct call from a client that intercepted the redirect itself
     * (POST) — same response shape as the normal login() either way.
     *
     * Creates the local user on first login (or updates the matching one)
     * via AvarewaseUserProvisioner; see its docblock for the matching and
     * creation rules.
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

    private function avarewaseCacheKey(string $state): string
    {
        return "avarewase_sso:verifier:{$state}";
    }

    public function logout()
    {
        $user = auth()->user();
        $token = $user->currentAccessToken();
        $token->delete();
        auth()->guard('web')->logout();

        return $this->apiResponse(message: 'logout successfuly');
    }

    private function generateToken($user, Request $request)
    {
        $deviceName = $this->deviceName($request);

        $user->tokens()->where('name', $deviceName)->delete();

        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Identifies the requesting device so a new login only replaces that
     * device's own token, leaving other tabs/devices logged in. Clients
     * may send a stable `device_id` (e.g. persisted in local storage);
     * otherwise we fall back to a hash of user agent + IP.
     */
    private function deviceName(Request $request): string
    {
        $deviceId = $request->string('device_id')->toString()
            ?: sha1($request->userAgent() . '|' . $request->ip());

        return config('app.name') . ':' . $deviceId;
    }
}
