<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggedCache;

class ProductCache
{
    public static function tags(int $tenantId): TaggedCache
    {
        return Cache::tags(["tenant:{$tenantId}", "products"]);
    }

    public static function indexKey(int $tenantId, int $page, int $perPage, array $includes): string
    {
        $inc = self::includesKey($includes);
        return "products:index:t{$tenantId}:p{$page}:pp{$perPage}:inc{$inc} ";
    }

    public static function showKey(int $tenantId, int $productId, array $includes): string
    {
        $inc = self::includesKey($includes);
        return "products:show:t{$tenantId}:id{$productId}:inc{$inc}";
    }

    public static function invalidateAll(int $tenantId): void
    {
        self::tags($tenantId)->flush();
    }

    private static function includesKey(array $includes): string
    {
        return implode('-', collect($includes)->sort()->all());
    }
}
