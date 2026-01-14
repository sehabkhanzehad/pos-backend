<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Tratis\HasRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    use HasRelationship;

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
                $this->buildIncluded(...)
            ),
        ];
    }

    private function buildIncluded(): void
    {
        $included = [];

        if ($this->relationLoaded('permissions')) {
            $included = array_merge($included, $this->permissions->map(function ($permission) {
                return [
                    'type' => 'permission',
                    'id' => $permission->id,
                    'attributes' => [
                        'name' => $permission->name,
                    ],
                ];
            })->toArray());
        }

        if ($this->relationLoaded('roles')) {
            $included = array_merge($included, $this->roles->map(function ($role) {
                return [
                    'type' => 'role',
                    'id' => $role->id,
                    'attributes' => [
                        'name' => $role->name,
                    ],
                ];
            })->toArray());
        }
    }
}
