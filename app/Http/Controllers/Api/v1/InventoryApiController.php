<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryMovementResource;
use App\Http\Resources\ProductResource;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    public function products(Request $request)
    {
        $products = Product::orderBy('name')->get();

        return ProductResource::collection($products);
    }

    public function showProduct(Product $product)
    {
        return new ProductResource($product);
    }

    public function movements(Request $request, Product $product)
    {
        return InventoryMovementResource::collection($product->movements()->with('product')->orderByDesc('created_at')->get());
    }

    public function storeMovement(Request $request)
    {
        $this->authorize('adjustStock', Product::class);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $movement = DB::transaction(function () use ($product, $validated) {
            $stockBefore = $product->current_stock;
            $stockAfter = $stockBefore + ($validated['movement_type'] === 'in' ? $validated['quantity'] : -$validated['quantity']);

            if ($stockAfter < 0) {
                abort(422, 'Insufficient stock.');
            }

            $product->update(['current_stock' => $stockAfter]);

            return InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => $validated['movement_type'],
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
            ]);
        });

        return new InventoryMovementResource($movement->load('product'));
    }
}
