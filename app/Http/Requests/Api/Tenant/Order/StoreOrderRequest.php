<?php

namespace App\Http\Requests\Api\Tenant\Order;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', existsInTenant('customers', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', existsInTenant('products', 'id')],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    public function getProductIds(): Collection
    {
        return collect($this->items)->pluck('product_id')->unique()->sort()->values();
    }
}
