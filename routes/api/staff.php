<?php

use App\Http\Controllers\StaffController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->can('viewAny', User::class);
    Route::post('/', [StaffController::class, 'store'])->can('create', User::class);

    Route::put('/{user}', [StaffController::class, 'update'])->can('update', 'user');
    Route::delete('/{user}', [StaffController::class, 'destroy'])->can('delete', 'user');
});
