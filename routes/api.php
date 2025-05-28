<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckOngkirController;
use App\Http\Controllers\KurirController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
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

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });

        Route::middleware('can:master-kurir')->group(function () {
            Route::get('kurir', [KurirController::class, 'get']);
            Route::post('kurir', [KurirController::class, 'index']);
            Route::post('kurir/store', [KurirController::class, 'store']);
            Route::apiResource('kurir', KurirController::class)
                ->except(['index', 'store']);
        });
    });

        Route::middleware('can:menu')->group(function () {
            Route::get('menu', [MenuController::class, 'get']);
            Route::post('menu', [MenuController::class, 'index']);
            Route::post('menu/store', [MenuController::class, 'store']);
            Route::apiResource('menu', MenuController::class)
                ->except(['index', 'store']);
        });

        Route::middleware('can:order')->group(function () {
            Route::get('order', [OrderController::class, 'get']);
            Route::post('order', [OrderController::class, 'index']);
            Route::post('order/store', [OrderController::class, 'store']);
            Route::apiResource('order', OrderController::class)
                ->except(['index', 'store']);
        });
        // Route::get('/ongkir', 'CheckOngkirController@index');
        // Route::post('/ongkir', 'CheckOngkirController@check_ongkir');
        // Route::get('/cities/{province_id}', 'CheckOngkirController@getCities');

        Route::get('/provinces', [OrderController::class, 'getProvinces']);
        Route::get('/cities/{provinceId}', [CheckOngkirController::class, 'getCities']);
        Route::post('/order/{id}', [OrderController::class, 'store']); // Untuk
        Route::post('/cost', [OrderController::class, 'hitungOngkir']);

        // Route::get('/provinces', [CheckOngkirController::class, 'getProvinces']);
        // Route::get('/cities/{province_id}', [CheckOngkirController::class, 'getCities']);
        // Route::post('/ongkir', [CheckOngkirController::class, 'check_ongkir']);



});
