<?php

use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;

Route::get('/test', fn() => response()->json(['message' => 'API is working.']))->middleware('throttle:15,1');

require __DIR__ . '/api/auth.php';

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Can manage only owner
    require __DIR__ . '/api/tenant.php';

    // Can manage owner and staff (based on permissions)
    Route::middleware(ResolveTenant::class)->group(function () {
        require __DIR__ . '/api/report.php';

        require __DIR__ . '/api/staff.php';
        require __DIR__ . '/api/role-permission.php';

        require __DIR__ . '/api/product.php';
        require __DIR__ . '/api/customer.php';
        require __DIR__ . '/api/order.php';

        require __DIR__ . '/api/setting.php';
    });
});
