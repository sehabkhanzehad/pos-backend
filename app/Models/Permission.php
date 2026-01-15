<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Permission as EnumPermission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Permission extends Model
{
    protected $casts = [
        'name' => EnumPermission::class,
    ];

    protected $fillable = ['name'];

    /**
     * Roles that have been assigned this permission.
     *
     * Returns roles that were given this permission via the 'model_permissions' pivot table.
     * This relation gathers the related roles assigned this permission. Access them with $permission->roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles(): MorphToMany
    {
        return $this->morphedByMany(Role::class, 'assignable', 'model_permissions');
    }

    /**
     * Users that have been assigned this permission.
     *
     * Returns users that were given this permission via the 'model_permissions' pivot table.
     * This relation gathers the related users assigned this permission. Access them with $permission->users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function staffs(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'assignable', 'model_permissions', 'permission_id', 'user_id');
    }
}
