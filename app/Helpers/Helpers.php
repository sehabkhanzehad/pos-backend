<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Context;

if (!function_exists('currentTenant')) {
    function currentTenant(): ?Tenant
    {
        return Context::getHidden('current_tenant');
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?User
    {
        return request()->user();
    }
}

if (!function_exists('perPage')) {
    function perPage()
    {
        $perPage = request('per_page');

        if (!is_numeric($perPage) || (int) $perPage <= 0) return 10;

        return min((int) $perPage, 50);
    }
}
