<?php

namespace App\Models;

use App\Models\Traits\HasOwnedRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasOwnedRoles;
    use HasUuids;

    protected $cast = [
        'default' => 'boolean',
    ];

    protected $guarded = ['id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function canAccess(User $user): bool
    {
        return $user->isOwner() ? $this->owner()->is($user) : $user->tenant()->is($this);
    }
}
