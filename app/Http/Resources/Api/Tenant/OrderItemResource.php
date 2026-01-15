<?php

namespace App\Http\Resources\Api\Tenant;

use App\Http\Resources\Traits\JsonApiRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'type' => 'order-item',
            'id' => $this->id,
            'attributes' => [
                'qty' => $this->qty,
                'unitPrice' => $this->unit_price,
                'subTotal' => $this->sub_total,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'product' => $this->relationship('product', 'product'),
                'order' => $this->relationship('order', 'order'),
            ],
            'included' => $this->when(
                $this->relationLoaded('product') || $this->relationLoaded('order'),
                $this->buildIncluded()
            ),
        ];
    }

    private function buildIncluded(): array
    {
        $included = collect();

        if ($this->relationLoaded('product')) {
            $included->push(ProductResource::make($this->product));
        }

        if ($this->relationLoaded('order')) {
            $included->push(OrderResource::make($this->order));
        }

        return $included->toArray();
    }
}
