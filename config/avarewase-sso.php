<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Avarewase SSO server
    |--------------------------------------------------------------------------
    */
    'base_url' => env('AVAREWASE_SSO_BASE_URL', 'https://auth.avarewase.com'),

    'authorize_path' => '/oauth/authorize',
    'token_path' => '/oauth/token',
    'userinfo_path' => '/api/userinfo',

    /*
    |--------------------------------------------------------------------------
    | OAuth client credentials
    |--------------------------------------------------------------------------
    | Registered via the Avarewase admin panel at /admin/clients.
    */
    'client_id' => env('AVAREWASE_SSO_CLIENT_ID'),
    'client_secret' => env('AVAREWASE_SSO_CLIENT_SECRET'),
    'redirect_uri' => env('AVAREWASE_SSO_REDIRECT_URI'),

    'scopes' => explode(' ', env('AVAREWASE_SSO_SCOPES', 'openid profile email')),

    /*
    |--------------------------------------------------------------------------
    | HTTP resilience
    |--------------------------------------------------------------------------
    | Applied to every call the package makes to the SSO server (token
    | exchange, userinfo, refresh). No local-login fallback is provided —
    | if the server is unreachable after retries, an
    | Avarewase\SsoClient\Exceptions\AvarewaseConnectionException is thrown.
    */
    'http' => [
        'timeout' => (int) env('AVAREWASE_SSO_HTTP_TIMEOUT', 5),
        'connect_timeout' => (int) env('AVAREWASE_SSO_HTTP_CONNECT_TIMEOUT', 3),
        'retry_times' => (int) env('AVAREWASE_SSO_HTTP_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('AVAREWASE_SSO_HTTP_RETRY_SLEEP_MS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Local session / guard
    |--------------------------------------------------------------------------
    | The guard name a consuming app should configure in config/auth.php
    | (session driver + eloquent provider). The package logs users into
    | this guard after a successful callback — it does not ship a custom
    | Guard class, it uses Laravel's normal session guard.
    */
    'guard' => env('AVAREWASE_SSO_GUARD', 'web'),

    'user_model' => env('AVAREWASE_SSO_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Resource-server middleware (auth.sso_or_sanctum)
    |--------------------------------------------------------------------------
    | How long (seconds) a validated Bearer token's userinfo response is
    | cached, keyed by a hash of the token. Used by
    | App\Http\Middleware\AuthenticateWithSsoOrSanctum when a request carries
    | a raw avarewase-sso access token (the mobile app) rather than an
    | eftkad-issued Sanctum token (the web SPA).
    */
    'cache_ttl' => (int) env('AVAREWASE_SSO_USERINFO_CACHE_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | User provisioning
    |--------------------------------------------------------------------------
    | eftkad matches/creates users by avarewase_sub, then membership_code,
    | then email — see App\Auth\AvarewaseUserProvisioner, bound over this
    | default in App\Providers\AppServiceProvider.
    */
    'provisioner' => \Avarewase\SsoClient\Auth\DefaultAvarewaseUserProvisioner::class,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | Disabled: eftkad wires its own /api/auth/avarewase/redirect+callback
    | (App\Http\Controllers\Api\AuthController) instead of the package's.
    */
    'routes' => [
        'enabled' => env('AVAREWASE_SSO_ROUTES_ENABLED', true),
        'prefix' => 'login/avarewase',
        'middleware' => ['web'],
        'login_name' => 'avarewase.login',
        'callback_name' => 'avarewase.callback',
        'redirect_after_login' => '/home',
    ],

    /*
    |--------------------------------------------------------------------------
    | Back-channel logout webhook
    |--------------------------------------------------------------------------
    | avarewase-sso's App\Jobs\NotifyClientLogoutJob POSTs a signed
    | `user.access_revoked` event here when an admin revokes this user's
    | access — set this client's "Logout Webhook URL" (on avarewase-sso) to
    | ${APP_URL}/login/avarewase/logout-webhook, and AVAREWASE_SSO_LOGOUT_SECRET
    | here to the logout_secret it issued for this client.
    */
    'logout_webhook' => [
        'enabled' => env('AVAREWASE_SSO_LOGOUT_WEBHOOK_ENABLED', true),
        'path' => env('AVAREWASE_SSO_LOGOUT_WEBHOOK_PATH', 'login/avarewase/logout-webhook'),
        'name' => 'avarewase.logout-webhook',
        'middleware' => ['throttle:30,1'],
        'secret' => env('AVAREWASE_SSO_LOGOUT_SECRET'),
    ],

];
