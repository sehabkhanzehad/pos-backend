<?php

use App\Http\Controllers\Api\Tenant\StaffController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Add routes for permission & role

Route::prefix('staffs')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->can('viewAny', User::class);
    Route::post('/', [StaffController::class, 'store'])->can('create', User::class);

    Route::put('/{user}', [StaffController::class, 'update'])->can('update', 'user');
    Route::delete('/{user}', [StaffController::class, 'destroy'])->can('delete', 'user');
});
