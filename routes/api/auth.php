<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/sign-up', [AuthController::class, 'signUp']);
        Route::post('/sign-in', [AuthController::class, 'signIn']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sign-out', [AuthController::class, 'signOut']);
    });
});
