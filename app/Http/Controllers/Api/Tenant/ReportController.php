<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Tenant\ReportResource;
use App\Jobs\TopProductsReportJob;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Retrieves daily sales summary for a specific date.
     * Includes total orders and revenue for paid orders.
     *
     * @return JsonResponse
     */
    public function dailySales(): JsonResponse
    {
        $date = request('date', now()->toDateString());

        $summary = Order::query()
            ->whereDate('created_at', $date)
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
            ->first();

        return response()->json([
            'date' => $date,
            'total_orders' => $summary->total_orders ?? 0,
            'total_revenue' => $summary->total_revenue ?? 0.00,
        ]);
    }

    /**
     * Retrieves top-selling products within a date range.
     * Uses background job for heavy queries; returns cached result if available.
     *
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function topProducts(): AnonymousResourceCollection|JsonResponse
    {
        $startDate = request('start_date', now()->subDays(30)->toDateString());
        $endDate = request('end_date', now()->toDateString());
        $cacheKey = "top_products_" . currentTenant()->id . "_{$startDate}_{$endDate}";

        $topProducts = Cache::get($cacheKey);

        if ($topProducts) return ReportResource::collection($topProducts);

        TopProductsReportJob::dispatch($startDate, $endDate, currentTenant()->id);

        return $this->success('Report is being generated. Please check back later.', 202);
    }

    /**
     * Retrieves products with low stock (below threshold).
     *
     * @return AnonymousResourceCollection
     */
    public function lowStock(): AnonymousResourceCollection
    {
        $lowStockProducts = Product::query()
            ->where('stock_qty', '<=', DB::raw('low_stock_threshold'))
            ->select('id', 'name', 'sku', 'stock_qty', 'low_stock_threshold')
            ->get();

        return ReportResource::collection($lowStockProducts ?? collect());
    }
}
