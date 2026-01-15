<?php

use App\Http\Controllers\Api\Tenant\OrderController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->can('viewAny', Order::class);
    Route::post('/', [OrderController::class, 'store'])->can('create', Order::class);

    Route::get('/{order}', [OrderController::class, 'show'])->can('view', 'order');
    Route::post('/{order}/paid', [OrderController::class, 'paid'])->can('paid', "order");
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->can('cancel', "order");
});
