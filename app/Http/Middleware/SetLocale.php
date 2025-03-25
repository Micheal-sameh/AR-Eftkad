<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // $lang = $request->route('lang', session('lang', 'en')); // Default to 'en' if not set
        $lang = $request->header('Accept-Language') ?? 'en';
        App::setLocale($lang);
        session(['lang' => $lang]);

        return $next($request);
    }
}
