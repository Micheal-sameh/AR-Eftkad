<?php

use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EftkadController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth.sso_or_sanctum')->get('/user', [UserController::class, 'me']);

Route::middleware('auth.sso_or_sanctum')->patch('/user/font-size', [UserController::class, 'updateFontSize']);

Route::view('/documentation', 'vendor.l5-swagger.index', [
    'documentation' => 'default',
    'useAbsolutePath' => true,
]);

Route::group(['prefix' => 'auth'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('avarewase/redirect', [AuthController::class, 'avarewaseRedirect'])->middleware('throttle:20,1');
    Route::match(['get', 'post'], 'avarewase/callback', [AuthController::class, 'avarewaseCallback'])->middleware('throttle:20,1');

    if (config('app.dev_login')) {
        Route::post('dev-login', [AuthController::class, 'devLogin'])->middleware('throttle:10,1');
    }
});
Route::group(['middleware' => 'auth.sso_or_sanctum'], function () {
    Route::group(['prefix' => 'eftkads'], function () {
        Route::get('home', [EftkadController::class, 'home']);
        Route::get('detail/{id}', [EftkadController::class, 'show']);
        Route::get('detail/{id}/share', [EftkadController::class, 'shareInfo']);
        Route::post('detail/{id}/share', [EftkadController::class, 'share']);
        Route::delete('detail/{id}/share/{code}', [EftkadController::class, 'unshare']);
        Route::get('{all?}', [EftkadController::class, 'index']);
        Route::post('', [EftkadController::class, 'create']);
    });

    Route::get('about', [AboutController::class, 'show']);
    Route::put('about', [AboutController::class, 'update'])->middleware('role:superadmin|owner');
    Route::post('about/image', [AboutController::class, 'uploadImage'])->middleware('role:superadmin|owner');

    Route::group(['prefix' => 'users', 'middleware' => 'role:admin|superadmin|owner'], function () {
        Route::get('', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::patch('{id}/type', [UserController::class, 'updateType']);
        Route::patch('{id}/role', [UserController::class, 'updateRole'])->middleware('role:superadmin|owner');
    });

    Route::group(['as' => 'api.', 'prefix' => 'settings'], function () {
        Route::get('/enums', [SettingController::class, 'enums']);
        Route::get('/filters', [SettingController::class, 'filters']);
    });
});
