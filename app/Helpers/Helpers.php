<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Context;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

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

if (!function_exists('existsInTenant')) {
    function existsInTenant($table, $column)
    {
        // Note: This function uses the current tenant
        return Rule::exists($table, $column)
            ->where('tenant_id', currentTenant()->id);
    }
}

if (!function_exists('includes')) {
    function includes(array $relations = []): array
    {
        $include = request('include');

        if (empty($include)) return $relations;

        $requestedRelations = explode(',', $include);

        return array_values(array_unique([...$relations, ...$requestedRelations]));
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

if (!function_exists('uniqueInTenant')) {
    function uniqueInTenant(string $table, string $column, $ignore = null): Unique
    {
        // Note: This function uses the current tenant
        return Rule::unique($table, $column)
            ->where('tenant_id', currentTenant()->id)
            ->ignore($ignore);
    }
}
