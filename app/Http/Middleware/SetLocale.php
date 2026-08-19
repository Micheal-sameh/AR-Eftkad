<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Explicit user choice (the `lang` cookie, set by the AR/EN toggle)
     * wins over the browser's Accept-Language header, which the client
     * can't control on a normal page navigation.
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = $request->cookie('lang')
            ?? explode(',', $request->header('Accept-Language') ?? 'ar')[0]
            ?? 'ar';

        $languageCode = in_array(substr($lang, 0, 2), ['ar', 'en']) ? substr($lang, 0, 2) : 'ar';

        App::setLocale($languageCode);
        session(['lang' => $languageCode]);

        return $next($request);
    }
}
