<?php

namespace App\Models;

use App\Models\Traits\HasOwnedRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasOwnedRoles;

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

    public function canAccess(User $user): bool
    {
        return $user->isOwner() ? $this->owner()->is($user) : $user->tenant()->is($this);
    }
}
