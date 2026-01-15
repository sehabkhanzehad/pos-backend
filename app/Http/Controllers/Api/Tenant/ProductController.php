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
            Product::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'price' => $request->price,
                'stock_qty' => $request->stock_qty,
                'low_stock_threshold' => $request->low_stock_threshold,
            ]);

            return $this->success('Product created successfully.');
        } catch (\Exception $e) {
            return $this->error("Failed to create product.");
        }
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->fill($request->only([
            'name',
            'sku',
            'price',
            'stock_qty',
            'low_stock_threshold',
            'status'
        ]));

        try {
            $product->save();
        } catch (\Exception $e) {
            //
        }

        return new ProductResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success('Product deleted successfully.');
    }
}
