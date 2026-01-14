<?php

use App\Http\Controllers\Api\Tenant\RolePermissionController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::prefix('roles')->group(function () {
    Route::get('/', [RolePermissionController::class, 'index'])->can("viewAny", Role::class);
    Route::post('/', [RolePermissionController::class, 'store'])->can('create', Role::class);

    Route::put('/{role}', [RolePermissionController::class, 'update'])->can('update', 'role');
    Route::delete('/{role}', [RolePermissionController::class, 'destroy'])->can('delete', 'role');
});

Route::get('/permissions', [RolePermissionController::class, 'permissions'])->can('viewAny', Role::class);
