<?php

namespace App\Models\Traits;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasRoles
{
    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'assignable', 'model_roles')->withTimestamps();
    }

    /* Helpers */
    public function assignRoles(array $roles): void
    {
        $this->roles()->sync($roles);
    }
}
