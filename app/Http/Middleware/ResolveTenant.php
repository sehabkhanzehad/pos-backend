<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (!$tenant || !$tenant->canAccess($request->user())) return $this->error('Invalid Tenant or Access Denied.', 403);

        Context::addHidden('current_tenant', $tenant);

        return $next($request);
    }
}
