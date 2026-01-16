<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Staff\StoreStaffRequest;
use App\Http\Requests\Api\Tenant\Staff\UpdateStaffRequest;
use App\Http\Resources\Api\Tenant\StaffResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $staffs = currentTenant()->staffs()
            ->where('id', '!=', currentUser()->id)
            ->with(includes())
            ->latest()
            ->paginate(perPage());

        return StaffResource::collection($staffs);
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $password = Str::random(8);

        $staff = currentTenant()->staffs()->create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => UserRole::Staff,
            'password' => $password,
        ]);

        $staff->assignRoles($request->roles);
        $staff->givePermissions($request->permissions ?? []);

        return $this->success('Staff added successfully.', 201, [
            'password' => $password
        ]);
    }

    public function show(User $user): StaffResource
    {
        return new StaffResource($user->load(includes()));
    }

    public function update(UpdateStaffRequest $request, User $user): JsonResponse
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->assignRoles($request->roles);
        $user->givePermissions($request->permissions ?? []);

        return $this->success('Staff updated successfully.');
    }

    public function destroy(User $user): JsonResponse
    {
        //Todo: Handle any cleanup if necessary

        $user->delete();

        return $this->success('Staff deleted successfully.');
    }
}
