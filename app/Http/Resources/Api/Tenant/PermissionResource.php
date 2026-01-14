<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'type' => 'permission',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
            ],
            'relationships' => [
                'roles' => $this->relationship('roles', 'role'),
                'users' => $this->relationship('users', 'user'),
            ],
            // included relationships
        ];
    }
}
