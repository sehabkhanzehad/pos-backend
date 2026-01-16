<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Creates a new order with items, validates stock, and updates inventory.
     *
     * @param array $data Validated order data including customer_id and items array.
     * @return Order The created order instance.
     * @throws InsufficientStockException If any product has insufficient stock.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;
            $orderItems = [];

            // Fetch and lock products for stock validation
            $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($data['items'] as $item) {
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
                    'sub_total' => $subtotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $data['customer_id'] ?? null,
                'order_no' => 'ORD-' . strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
                'created_by' => currentUser()->id,
            ]);

            $order->items()->createMany($orderItems);

            // Decrement stock after order creation
            foreach ($data['items'] as $item) {
                $products[$item['product_id']]->decrement('stock_qty', $item['qty']);
            }

            return $order;
        });
    }

    /**
     * Cancels an order and restores the stock quantities.
     *
     * @param Order $order The order to cancel.
     * @return void
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $productIds = $order->items->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($order->items as $item) {
                $products[$item->product_id]->increment('stock_qty', $item->qty);
            }

            $order->markAsCancelled();
        });
    }
}
