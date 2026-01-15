<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Enums\OrderStatus;
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

            $products = Product::whereIn('id', $request->getProductIds())->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];

                if ($product->stock_qty < $item['qty']) throw new \Exception("Insufficient stock for product {$product->name}.");

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
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
                'created_by' => currentUser()->id,
            ]);

            $order->items()->createMany($orderItems);

            // Decrement after order creation
            foreach ($request->items as $item) {
                $products[$item['product_id']]->decrement('stock_qty', $item['qty']);
            }

            DB::commit();

            return $this->success("Order created successfully.", 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed to create order.", 500);
        }
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(includes()));
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
                $product->increment('stock_qty', $item->qty);
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
