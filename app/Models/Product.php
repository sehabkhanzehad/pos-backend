<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'price',
        'stock_qty',
        'low_stock_threshold',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function incrementStock(int $quantity): void
    {
        $this->increment('stock_qty', $quantity);
    }
}
