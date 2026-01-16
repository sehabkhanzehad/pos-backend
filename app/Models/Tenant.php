<?php

namespace App\Models;

use App\Models\Traits\HasOwnedRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;
    use HasOwnedRoles;
    use HasUuids;

    protected $casts = [
        'default' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'default',
    ];

    // Relations
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

    // Helpers
    public function canAccess(User $user): bool
    {
        return $user->isOwner() ? $this->owner()->is($user) : $user->tenant()->is($this);
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }
}
