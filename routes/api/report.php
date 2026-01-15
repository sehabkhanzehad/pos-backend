<?php

use App\Http\Controllers\Api\Tenant\ReportController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->middleware('throttle:30,1')->group(function () {
    Route::get('/daily-sales', [ReportController::class, 'dailySales'])->can('viewAny', Order::class);
    Route::get('/top-products', [ReportController::class, 'topProducts'])->can('viewAny', Order::class);
    Route::get('/low-stock', [ReportController::class, 'lowStock'])->can('viewAny', Order::class);
});
