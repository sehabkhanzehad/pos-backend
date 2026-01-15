<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TenantResource::collection(
            currentUser()
                ->tenants()
                ->latest()
                ->paginate(perPage())
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255']
        ]);

        currentUser()->createTenant($request->name);

        return $this->success('Tenant created successfully.', 201);
    }

    public function show(Tenant $tenant): TenantResource
    {
        return new TenantResource($tenant);
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255']
        ]);

        $tenant->update([
            'name' => $request->name,
        ]);

        return $this->success('Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        //Todo: Handle any cleanup if necessary

        $tenant->delete();

        return $this->success('Tenant deleted successfully.');
    }
}
