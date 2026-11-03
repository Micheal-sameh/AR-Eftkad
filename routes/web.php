<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| Auth for these pages is entirely client-side (Sanctum token kept in
| localStorage) - these routes only render the Blade shell, the page's
| own JavaScript talks to the JSON API under /api and redirects to
| /login when there is no token.
|
*/

// These routes only return static Blade shells (auth is client-side), so skip the
// session/CSRF middleware: otherwise every page view reads and writes a session file.
Route::withoutMiddleware([
    StartSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
])->group(function () {
    Route::redirect('/', '/visits');

    Route::view('/login', 'auth.login')->name('login');
    Route::view('/login/avarewase/callback', 'auth.avarewase-callback')->name('login.avarewase.callback');

    // /visits is the home page (what's new for me + my recent visits); the full list with filters is /visits/all.
    Route::view('/visits', 'eftkads.home')->name('home');
    Route::view('/visits/all', 'eftkads.index')->name('visits.index');
    Route::view('/visits/create', 'eftkads.create')->name('visits.create');
    Route::view('/visits/{id}', 'eftkads.show')->name('visits.show');

    Route::view('/directory', 'directory.index')->name('directory.index');

    Route::view('/users', 'users.index')->name('users.index');
    // Editing a user's type/role happens in a modal on the users list.
    Route::redirect('/users/{id}', '/users');
    Route::redirect('/users/{id}/edit', '/users');
});
