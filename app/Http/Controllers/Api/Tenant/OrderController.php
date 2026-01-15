<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Enums\OruderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Order\StoreOrderRequest;
use App\Http\Resources\Api\Tenant\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::with(includes())
            ->latest()
            ->paginate(perPage()));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['qty']) throw new \Exception("Insufficient stock for product ID: {$product->id}");

                $subtotal = $product->price * $item['qty'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock_qty', $item['qty']);
            }

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'status' => OruderStatus::Pending,
                'created_by' => $request->user()->id,
            ]);

            $order->items()->createMany($orderItems);

            DB::commit();

            return $this->success("Order created successfully.", 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error("Failed to create order.", 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['customer', 'creator', 'items.product']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function paid(Order $order): JsonResponse
    {
        $order->markAsPaid();

        return $this->success("Order marked as paid successfully.");
    }

    public function cancel(Order $order): JsonResponse
    {
        try {
            DB::beginTransaction();

            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock_qty', $item->quantity);
            }

            $order->markAsCancelled();

            DB::commit();

            return $this->success("Order cancelled successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error("Failed to cancel order.", 500);
        }
    }
}
