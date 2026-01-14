<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'type' => 'role',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'permissions' => $this->relationship('permissions', 'permission'),
                'users' => $this->relationship('users', 'user'),
            ],
            'included' => $this->when(
                $this->relationLoaded('permissions') || $this->relationLoaded('users'),
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

        if ($this->relationLoaded('users')) {
            $included = $included->merge($this->users->map(StaffResource::make(...)));
        }

        return $included->toArray();
    }
}
