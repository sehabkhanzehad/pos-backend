<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        $user = $request->user();

        if (!$tenant || !$tenant->canAccess($user)) return response()->json([
            'message' => 'You do not have access to this tenant.'
        ], 403);

        Context::addHidden('current_tenant', $tenant);

        return $next($request);
    }
}
