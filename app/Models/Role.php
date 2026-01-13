<?php

namespace App\Models;

use App\Models\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends Model
{
    use HasPermissions;

    protected $fillable = ['name', 'roleable_id', 'roleable_type'];

    /**
     * Get the owner of this role.
     *
     * The "roleable" relation is polymorphic: a role can belong to different model types
     * (for example a User, a Tenant, etc.). Use $role->roleable to access the model that owns this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function roleable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Users that have been assigned this role.
     *
     * Returns users that were given this role via the 'model_roles' pivot table.
     * This relation gathers the related users assigned this role. Access them with $role->users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'assignable', 'model_roles');
    }
}
