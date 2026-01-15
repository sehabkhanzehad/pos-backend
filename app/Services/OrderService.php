<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\Api\Tenant\Order\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(StoreOrderRequest $request): Order
    {
        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $orderItems = [];

            $products = Product::whereIn('id', $request->getProductIds())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];

                if ($product->stock_qty < $item['qty']) {
                    throw new InsufficientStockException("Insufficient stock for product {$product->name}.");
                }

                $subtotal = $product->price * $item['qty'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $data['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
                'created_by' => currentUser()->id,
            ]);

            $order->items()->createMany($orderItems);

            // Decrement stock
            foreach ($request->items as $item) {
                $products[$item['product_id']]->decrement('stock_qty', $item['qty']);
            }

            return $order;
        });
    }

    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock_qty', $item->qty);
            }

            $order->markAsCancelled();
        });
    }
}
