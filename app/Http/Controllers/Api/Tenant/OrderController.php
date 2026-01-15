<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Order\StoreOrderRequest;
use App\Http\Resources\Api\Tenant\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * Retrieves a paginated list of orders with related data.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::with(includes())
            ->latest()
            ->paginate(perPage()));
    }

    /**
     * Creates a new order using the OrderService.
     *
     * @param StoreOrderRequest $request Validated request data.
     * @param OrderService $orderService Service for order creation logic.
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request, OrderService $orderService): JsonResponse
    {
        try {
            $orderService->createOrder($request->validated());

            return $this->success('Order created successfully.', 201);
        } catch (\Exception $e) {
            logger()->error("Order creation failed: {$e->getMessage()}");
            return $this->error('Failed to create order.', 500);
        }
    }

    /**
     * Retrieves details of a specific order.
     *
     * @param Order $order The order instance.
     * @return JsonResponse|OrderResource
     */
    public function show(Order $order): JsonResponse|OrderResource
    {
        try {
            return new OrderResource($order->load(includes()));
        } catch (\Exception $e) {
            return $this->error("Failed to retrieve order details.", 500);
        }
    }

    /**
     * Marks an order as paid.
     *
     * @param Order $order The order to mark as paid.
     * @return JsonResponse
     */
    public function paid(Order $order): JsonResponse
    {
        try {
            $order->markAsPaid();

            return $this->success("Order marked as paid successfully.");
        } catch (\Exception $e) {
            logger()->error("Order payment failed: {$e->getMessage()}");
            return $this->error('Failed to mark order as paid.', 500);
        }
    }

    /**
     * Cancels an order and restores stock using OrderService.
     *
     * @param Order $order The order to cancel.
     * @param OrderService $orderService Service for cancellation logic.
     * @return JsonResponse
     */
    public function cancel(Order $order, OrderService $orderService): JsonResponse
    {
        try {
            $orderService->cancelOrder($order);

            return $this->success("Order cancelled successfully.");
        } catch (\Exception $e) {
            logger()->error("Order cancellation failed: {$e->getMessage()}");
            return $this->error("Failed to cancel order.", 500);
        }
    }
}
