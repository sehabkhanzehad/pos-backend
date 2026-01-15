<?php

use App\Http\Controllers\Api\Tenant\CustomerController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->can('viewAny', Customer::class);
    Route::post('/', [CustomerController::class, 'store'])->can('create', Customer::class);
    Route::put('/{customer}', [CustomerController::class, 'update'])->can('update', "customer");
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->can('delete', 'customer');
});
