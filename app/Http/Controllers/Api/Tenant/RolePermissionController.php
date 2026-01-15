<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Role\StoreRoleRequest;
use App\Http\Requests\Api\Tenant\Role\UpdateRoleRequest;
use App\Http\Resources\Api\Tenant\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RolePermissionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $roles = currentTenant()
            ->ownedRoles()
            ->select('id', 'name', 'created_at', 'updated_at')
            ->with(includes())
            ->latest()
            ->paginate(perPage());

        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = currentTenant()->createRole($request->name);

        $role->givePermissions($request->permissions);

        return $this->success('Role created successfully', 201);
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update([
            'name' => $request->name
        ]);

        $role->givePermissions($request->permissions);

        return $this->success('Role updated successfully.');
    }

    public function destroy(Role $role): JsonResponse
    {
        //Todo: Handle any cleanup if necessary

        $role->delete();

        return $this->success('Role deleted successfully.');
    }

    public function roles(): AnonymousResourceCollection
    {
        $roles = currentTenant()
            ->ownedRoles()
            ->select('id', 'name', 'created_at', 'updated_at')
            ->orderBy('name')
            ->get();

        return RoleResource::collection($roles);
    }

    public function permissions(): JsonResponse
    {
        $permissions = array_map(function ($permission) {
            return (object)['name' => $permission];
        }, Permission::values());

        return response()->json([
            "permissions" => $permissions,
        ]);
    }
}
