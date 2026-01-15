<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Product\StoreProductRequest;
use App\Http\Requests\Api\Tenant\Product\UpdateProductRequest;
use App\Http\Resources\Api\Tenant\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->latest()
                ->paginate(perPage())
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'price' => $request->price,
                'stock_qty' => $request->stock_qty,
                'low_stock_threshold' => $request->low_stock_threshold,
            ]);

            return $this->success('Product created successfully.', data: [
                'product' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            logger()->error("Product creation failed: {$e->getMessage()}");
            return $this->error('Failed to create product.', 500);
        }
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $product->update($request->validated());

            return $this->success('Product updated successfully.', data: [
                'product' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            logger()->error("Product update failed: {$e->getMessage()}");
            return $this->error('Failed to update product.', 500);
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->delete();

            return $this->success('Product deleted successfully.');
        } catch (\Exception $e) {
            logger()->error("Product deletion failed: {$e->getMessage()}");
            return $this->error('Failed to delete product.', 500);
        }
    }
}
