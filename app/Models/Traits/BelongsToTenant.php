<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function bootBelongsToTenant(): void
    {
        if (!currentTenant()) return;

        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            $model->tenant_id = currentTenant()->id;
        });
    }
}
