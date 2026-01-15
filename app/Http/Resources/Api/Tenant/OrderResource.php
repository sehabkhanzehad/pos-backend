<?php

namespace App\Http\Resources\Api\Tenant;

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
        ];
    }
}
