<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'type' => 'order',
            'id' => $this->id,
            'attributes' => [
                'orderNo' => $this->order_no,
                'status' => $this->status,
                'totalAmount' => $this->total_amount,
                'paidAt' => $this->paid_at,
                'cancelledAt' => $this->cancelled_at,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'customer' => $this->relationship('customer', 'customer'),
                'creator' => $this->relationship('creator', 'user'),
                'items' => $this->relationship('items', 'order-item'),
            ],
            'included' => $this->when(
                $this->relationLoaded('customer') || $this->relationLoaded('creator') || $this->relationLoaded('items'),
                $this->buildIncluded()
            ),
        ];
    }

    private function buildIncluded(): array
    {
        $included = collect();

        if ($this->relationLoaded('customer')) {
            $included->push(CustomerResource::make($this->customer));
        }

        if ($this->relationLoaded('creator')) {
            $included->push(UserResource::make($this->creator));
        }

        if ($this->relationLoaded('items')) {
            $included = $included->merge($this->items->map(OrderItemResource::make(...)));
        }

        return $included->toArray();
    }
}
