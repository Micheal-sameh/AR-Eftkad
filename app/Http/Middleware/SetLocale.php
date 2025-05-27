<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // $lang = $request->route('lang', session('lang', 'ar')); // Default to 'ar' if not set
        $lang = $request->header('Accept-Language') ?? 'ar';
        $languageCode = explode(',', $lang)[0];
        App::setLocale($languageCode);
        session(['lang' => $lang]);

        return $next($request);
    }
}
