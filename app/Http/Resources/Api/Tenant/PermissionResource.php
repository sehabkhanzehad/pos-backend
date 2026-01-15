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
                'staffs' => $this->relationship('staffs', 'staff'),
            ],
            'included' => $this->when(
                $this->relationLoaded('roles') || $this->relationLoaded('staffs'),
                $this->buildIncluded()
            ),
        ];
    }

    private function buildIncluded(): array
    {
        $included = collect();

        if ($this->relationLoaded('roles')) {
            $included = $included->merge($this->roles->map(RoleResource::make(...)));
        }
        if ($this->relationLoaded('staffs')) {
            $included = $included->merge($this->staffs->map(StaffResource::make(...)));
        }

        return $included->toArray();
    }
}
