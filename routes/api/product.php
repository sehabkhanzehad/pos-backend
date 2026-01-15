<?php

use App\Http\Controllers\Api\Tenant\ProductController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->can('viewAny', Product::class);
    Route::post('/', [ProductController::class, 'store'])->can('create', Product::class);

    Route::get('/{product}', [ProductController::class, 'show'])->can('view', "product");
    Route::put('/{product}', [ProductController::class, 'update'])->can('update', "product");
    Route::delete('/{product}', [ProductController::class, 'destroy'])->can('delete', 'product');
});
