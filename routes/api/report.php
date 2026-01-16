<?php

use App\Http\Controllers\Api\Tenant\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('/daily-sales', [ReportController::class, 'dailySales']);
    Route::get('/top-products', [ReportController::class, 'topProducts']);
    Route::get('/low-stock', [ReportController::class, 'lowStock']);
});
