<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware(['guest:sanctum', 'throttle:10,1'])->group(function () { // Guest middleware aliased in bootstrap/app.php
        Route::post('/sign-up', [AuthController::class, 'signUp']);
        Route::post('/sign-in', [AuthController::class, 'signIn']);
    });

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/sign-out', [AuthController::class, 'signOut']);
    });
});
