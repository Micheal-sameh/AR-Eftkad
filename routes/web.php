<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect('/visits');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/login/avarewase/callback', function () {
    return view('auth.avarewase-callback');
})->name('login.avarewase.callback');

Route::get('/visits', function () {
    return view('eftkads.index');
})->name('visits.index');

Route::get('/visits/create', function () {
    return view('eftkads.create');
})->name('visits.create');

Route::get('/visits/{id}', function ($id) {
    return view('eftkads.show', ['id' => $id]);
})->name('visits.show');

Route::get('/directory', function () {
    return view('directory.index');
})->name('directory.index');
