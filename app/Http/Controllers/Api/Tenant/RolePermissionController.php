<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $roles = currentWorkspace()->ownedRoles()
            ->with(includes())
            ->latest()
            ->paginate(perPage());

        $perPageOptions = [10, 25, 50, 100];

        return RoleResource::collection($roles)->additional([
            'meta' => [
                'per_page_options' => $perPageOptions,
            ],
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = currentTenant()->createRole($request->name);

        $role->givePermissions($request->permissions);

        return $this->success('Role created successfully', 201);
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
}
