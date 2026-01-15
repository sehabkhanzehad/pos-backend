<?php

namespace App\Models;

use App\Enums\OruderStatus;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_no',
        'status',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'status' => OruderStatus::class,
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relations
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === OruderStatus::Pending;
    }

    public function isPaid(): bool
    {
        return $this->status === OruderStatus::Paid;
    }

    public function isCancelled(): bool
    {
        return $this->status === OruderStatus::Cancelled;
    }

    public function markAsCancelled(): void
    {
        $this->status = OruderStatus::Cancelled;
        $this->cancelled_at = now();
        $this->save();
    }

    public function markAsPaid(): void
    {
        $this->status = OruderStatus::Paid;
        $this->paid_at = now();
        $this->save();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }
}
