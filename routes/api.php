<?php

use Illuminate\Support\Facades\Route;

// require __DIR__ . '/api/auth.php';


Route::middleware('auth:sanctum')->group(function () {
    // require __DIR__ . '/api/tenant.php';

    // Route::middleware([ResolveTenant::class])->group(function () {
    //     require __DIR__ . '/api/staff.php';
    // });
});
