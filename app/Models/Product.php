<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'price',
        'stock_qty',
        'low_stock_threshold',
        'status',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relations
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
