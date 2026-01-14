<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Context;

if (!function_exists('currentTenant')) {
    function currentTenant(): ?Tenant
    {
        return Context::getHidden('current_tenant');
    }
}
