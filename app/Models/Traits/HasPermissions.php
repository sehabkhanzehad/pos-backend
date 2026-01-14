<?php

namespace App\Models\Traits;

use App\Enums\Permission as EnumPermission;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasPermissions
{
    // Relations
    /**
     * Permissions assigned to the model.
     *
     * Returns a polymorphic many-to-many relation to Permission models through the
     * 'model_permissions' pivot table. Use this to list, attach, or detach permissions
     * for the model (e.g. $model->permissions, $model->permissions()->attach($permissionId)).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'assignable', 'model_permissions')->withTimestamps();
    }

    // Helpers 
    public function givePermissions(array $permissions): void
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $this->permissions()->sync($permissionIds);
    }

    public function hasPermission(EnumPermission $permission): bool
    {
        $permissionName = $permission->value;

        // Direct permissions
        $direct = $this->permissions->contains('name', $permissionName);

        // Permissions via roles (requires model to use HasRoles trait)
        $viaRoles = method_exists($this, 'roles')
            ? $this->roles->flatMap->permissions->contains('name', $permissionName)
            : false;

        return $direct || $viaRoles;
    }
}
