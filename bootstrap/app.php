<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use App\Exceptions\InsufficientStockException;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Traits\ApiResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) return ApiResponse::error('This action is unauthorized.',  403,);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) return ApiResponse::error('Validation failed.', 422, errors: $e->errors());
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) return ApiResponse::error('Resource not found.', 404);
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->expectsJson()) return ApiResponse::error('A database error occurred.', 500, e: $e);
        });

        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            if ($request->expectsJson()) return ApiResponse::error($e->getMessage(), 400);
        });

        $exceptions->render(function (\Exception $e, Request $request) {
            if ($request->expectsJson()) return ApiResponse::error('An unexpected error occurred.', 500, e: $e);
        });
    })->create();
