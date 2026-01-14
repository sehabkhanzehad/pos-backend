<?php

namespace App\Http\Resources\Api\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
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
                'permissions' => [
                    'data' => $this->whenLoaded('permissions', function () {
                        return $this->permissions->map(function ($permission) {
                            return [
                                'type' => 'permission',
                                'id' => $permission->id,
                            ];
                        });
                    }),
                ],
                'roles' => [
                    'data' => $this->whenLoaded('roles', function () {
                        return $this->roles->map(function ($role) {
                            return [
                                'type' => 'role',
                                'id' => $role->id,
                            ];
                        });
                    }),
                ],
            ],
            'included' =>
            $this->when(
                $this->relationLoaded('permissions') || $this->relationLoaded('roles'),
                function () {
                    $included = [];

                    if ($this->relationLoaded('permissions')) {
                        foreach ($this->permissions as $permission) {
                            $included[] = [
                                'type' => 'permission',
                                'id' => $permission->id,
                                'attributes' => [
                                    'name' => $permission->name,
                                ],
                            ];
                        }
                    }

                    if ($this->relationLoaded('roles')) {
                        foreach ($this->roles as $role) {
                            $included[] = [
                                'type' => 'role',
                                'id' => $role->id,
                                'attributes' => [
                                    'name' => $role->name,
                                    'permissionsCount' => $role->permissions()->count(),
                                ],
                            ];
                        }
                    }
                    return $included;
                }
            ),
        ];
    }
}
