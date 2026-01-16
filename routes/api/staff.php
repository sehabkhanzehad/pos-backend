<?php

use App\Http\Controllers\Api\Tenant\StaffController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('staffs')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->can('viewAny', User::class);
    Route::post('/', [StaffController::class, 'store'])->can('create', User::class);

    Route::get('/{user}', [StaffController::class, 'show'])->can('view', 'user');
    Route::put('/{user}', [StaffController::class, 'update'])->can('update', 'user');
    Route::delete('/{user}', [StaffController::class, 'destroy'])->can('delete', 'user');
});
