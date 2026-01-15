<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'type' => 'customer',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'orders' => $this->relationship('orders', 'order'),
            ],
            'included' => $this->when($this->relationLoaded('orders'), $this->buildIncluded()),
        ];
    }

    private function buildIncluded(): array
    {
        $included = collect();

        if ($this->relationLoaded('orders')) {
            $included = $included->merge($this->orders->map(OrderResource::make(...)));
        }

        return $included->toArray();
    }
}
