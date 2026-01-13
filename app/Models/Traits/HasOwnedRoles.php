<?php

namespace App\Models\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasOwnedRoles
{
    // Relations
    public function ownedRoles(): MorphMany
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    // Helpers
    public function createRole(string $name): Role
    {
        return $this->ownedRoles()->create([
            'name' => $name
        ]);
    }
}
