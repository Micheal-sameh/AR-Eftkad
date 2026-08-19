<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Set directly via document.cookie by the AR/EN toggle (api-js.blade.php),
        // not through Laravel's cookie jar - encrypting it means SetLocale can
        // never decrypt it back and silently falls back to the default locale.
        'lang',
    ];
}
