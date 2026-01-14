<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    use JsonApiRelationship;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'staff',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'roles' => $this->relationship('roles', 'role'),
                'permissions' => $this->relationship('permissions', 'permission')
            ],
            'included' => $this->when(
                $this->relationLoaded('permissions') || $this->relationLoaded('roles'),
                $this->buildIncluded()
            ),
        ];
    }

    private function buildIncluded(): array
    {
        $included = collect();

        if ($this->relationLoaded('permissions')) {
            $included = $included->merge($this->permissions->map(PermissionResource::make(...)));
        }

        if ($this->relationLoaded('roles')) {
            $included = $included->merge($this->roles->map(RoleResource::make(...)));
        }

        return $included->toArray();
    }
}
