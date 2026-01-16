<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TopProductsReportJob implements ShouldQueue
{
    use Queueable;

    private string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected string $tenantId
    ) {
        $this->cacheKey = "top_products_{$tenantId}_{$startDate}_{$endDate}";
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->where('products.tenant_id', $this->tenantId)
            ->selectRaw('products.id, products.name, SUM(order_items.qty) as total_sold')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        Cache::put($this->cacheKey, $topProducts, 3600); // Cache the result for 1 hour
    }
}
