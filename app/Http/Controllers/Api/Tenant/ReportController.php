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
    public function dailySales(): JsonResponse
    {
        $date = request('date', now()->toDateString());

        $summary = Order::query()
            ->whereDate('created_at', $date)
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
            ->first();

        return $this->success('Daily sales summary retrieved.', data: [
            'summary' => $summary
        ]);
    }

    public function topProducts(): AnonymousResourceCollection
    {
        $startDate = request('start_date', now()->subDays(30)->toDateString());
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
            ->limit(5)
            ->get();

        return ReportResource::collection($topProducts);
    }

    public function lowStock(): AnonymousResourceCollection
    {
        $lowStockProducts = Product::query()
            ->where('stock_qty', '<=', DB::raw('low_stock_threshold'))
            ->select('id', 'name', 'sku', 'stock_qty', 'low_stock_threshold')
            ->get();

        return ReportResource::collection($lowStockProducts);
    }
}
