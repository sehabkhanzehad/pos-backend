<?php

use App\Http\Controllers\Api\Tenant\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('/settings')->group(function () {

    Route::prefix('account')->group(function () {
        Route::post('/profile', [SettingController::class, 'updateProfile']);
        Route::post('/password', [SettingController::class, 'updatePassword']);
    });

    Route::prefix('tenant')->group(function () {
        Route::post('/reset', [SettingController::class, 'resetTenant']);
    });
});
