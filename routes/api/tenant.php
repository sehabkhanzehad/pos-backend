<?php

use App\Http\Controllers\Api\TenantController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

Route::prefix('tenants')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->can('viewAny', Tenant::class);
    Route::post('/', [TenantController::class, 'store'])->can('create', Tenant::class);

    Route::get('/{tenant}', [TenantController::class, 'show'])->can('view', 'tenant');
    Route::put('/{tenant}', [TenantController::class, 'update'])->can('update', 'tenant');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->can('delete', 'tenant');
});
