<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated as Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated extends Middleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? ['web', 'sanctum'] : $guards;

        foreach ($guards as $guard) {
            if ($guard === 'sanctum' && $request->bearerToken()) return $this->error('You are already authenticated.', 403);

            //Todo: Handle other guards if needed
        }

        return $next($request);
    }
}
