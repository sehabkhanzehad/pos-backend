<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Tenant\ReportResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private const TOP_PRODUCTS_LIMIT = 5;
    private const DEFAULT_DATE_RANGE_DAYS = 30;

    /**
     * Retrieves daily sales summary for a specific date.
     * Includes total orders and revenue for paid orders.
     *
     * @return JsonResponse
     */
    public function dailySales(): JsonResponse
    {
        try {
            $date = request('date', now()->toDateString());

            $summary = Order::query()
                ->whereDate('created_at', $date)
                ->where('status', 'paid')
                ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
                ->first();

            return $this->success('Daily sales summary retrieved.', data: [
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            \Log::error('Daily sales report failed: ' . $e->getMessage());
            return $this->error('Failed to retrieve daily sales.', 500);
        }
    }

    /**
     * Retrieves top-selling products within a date range.
     * Joins order_items, orders, and products to calculate total sold quantity.
     *
     * @return AnonymousResourceCollection
     */
    public function topProducts(): AnonymousResourceCollection
    {
        try {
            $startDate = request('start_date', now()->subDays(self::DEFAULT_DATE_RANGE_DAYS)->toDateString());
            $endDate = request('end_date', now()->toDateString());

            $topProducts = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'paid')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('products.tenant_id', currentTenant()->id)
                ->selectRaw('products.id, products.name, SUM(order_items.qty) as total_sold')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(self::TOP_PRODUCTS_LIMIT)
                ->get();

            return ReportResource::collection($topProducts);
        } catch (\Exception $e) {
            \Log::error('Top products report failed: ' . $e->getMessage());
            // For collections, return empty or error, but since it's AnonymousResourceCollection, perhaps throw or handle differently.
            // But to keep simple, log and return empty.
            return ReportResource::collection(collect());
        }
    }

    /**
     * Retrieves products with low stock (below threshold).
     *
     * @return AnonymousResourceCollection
     */
    public function lowStock(): AnonymousResourceCollection
    {
        try {
            $lowStockProducts = Product::query()
                ->where('stock_qty', '<=', DB::raw('low_stock_threshold'))
                ->select('id', 'name', 'sku', 'stock_qty', 'low_stock_threshold')
                ->get();

            return ReportResource::collection($lowStockProducts);
        } catch (\Exception $e) {
            \Log::error('Low stock report failed: ' . $e->getMessage());
            return ReportResource::collection(collect());
        }
    }
}
