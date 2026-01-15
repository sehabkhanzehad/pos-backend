<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'type' => 'product',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'sku' => $this->sku,
                'price' => $this->price,
                'description' => $this->description,
                'stockQty' => $this->stock_qty,
                'lowStockThreshold' => $this->low_stock_threshold,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'orderItems' => $this->relationship('orderItems', 'order-item'),
            ],
            'included' => $this->when(
                $this->relationLoaded('orderItems'),
                $this->buildIncluded()
            ),
        ];
    }

    private function buildIncluded(): array
    {
        $included = collect();

        if ($this->relationLoaded('orderItems')) {
            $included = $included->merge($this->orderItems->map(OrderItemResource::make(...)));
        }

        return $included->toArray();
    }
}
