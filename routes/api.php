<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EftkadController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/documentation', function () {
    $documentation = config('l5-swagger.documentation') ?? 'default';
    $useAbsolutePath = true;

    return view('vendor.l5-swagger.index', compact('documentation', 'useAbsolutePath'));
});

Route::group(['middleware' => 'setlocale'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'eftkads'], function () {
            Route::get('detail/{id}', [EftkadController::class, 'show']);
            Route::get('{all?}', [EftkadController::class, 'index']);
            Route::post('', [EftkadController::class, 'create']);
        });

        Route::group(['as' => 'api.', 'prefix' => 'settings'], function () {
            Route::get('/enums', [SettingController::class, 'enums']);
            Route::get('/filters', [SettingController::class, 'filters']);
        });
    });
});
