<?php

namespace App\Http\Resources\Api\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
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
                'users' => [
                    'data' => $this->whenLoaded('models', function () {
                        return $this->models->map(function ($model) {
                            return [
                                'type' => 'model',
                                'id' => $model->id,
                            ];
                        });
                    }),
                ],
            ],
            'included' => $this->when(
                $this->relationLoaded('permissions') || $this->relationLoaded('models'),
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

                    if ($this->relationLoaded('users')) {
                        foreach ($this->users as $user) {
                            $included[] = [
                                'type' => 'user',
                                'id' => $user->id,
                                'attributes' => [
                                    'name' => $user->name,
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
