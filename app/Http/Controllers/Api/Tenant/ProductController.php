<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Product\StoreProductRequest;
use App\Http\Requests\Api\Tenant\Product\UpdateProductRequest;
use App\Http\Resources\Api\Tenant\ProductResource;
use App\Models\Product;
use App\Support\ProductCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cacheKey = ProductCache::indexKey(currentTenant()->id, (int) request('page', 1), perPage(), includes());

        $products = ProductCache::tags(currentTenant()->id)->remember($cacheKey, 900, function () {
            return Product::query()
                ->with(includes())
                ->latest()
                ->paginate(perPage());
        });

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        ProductCache::invalidateAll(currentTenant()->id);

        return $this->success('Product created successfully.', data: [
            'product' => new ProductResource($product)
        ]);
    }

    public function show(Product $product): ProductResource
    {
        $cacheKey = ProductCache::showKey(currentTenant()->id, $product->id, includes());

        $product = ProductCache::tags(currentTenant()->id)->remember($cacheKey, 3600, function () use ($product) {
            return $product->load(includes());
        });

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        ProductCache::invalidateAll(currentTenant()->id);

        return $this->success('Product updated successfully.', data: [
            'product' => new ProductResource($product->fresh()),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        ProductCache::invalidateAll(currentTenant()->id);

        return $this->success('Product deleted successfully.');
    }
}
