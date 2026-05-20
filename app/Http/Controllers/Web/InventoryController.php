<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('movements')->orderBy('name')->get();
        $selectedProduct = null;
        $movements = [];

        if ($request->filled('product_id')) {
            $selectedProduct = Product::with('movements')->find($request->product_id);
            $movements = $selectedProduct ? $selectedProduct->movements : [];
        }

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'movements' => $movements,
        ]);
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

        DB::transaction(function () use ($product, $validated) {
            $stockBefore = $product->current_stock;
            $stockAfter = $stockBefore + ($validated['movement_type'] === 'in' ? $validated['quantity'] : -$validated['quantity']);

            if ($stockAfter < 0) {
                abort(422, 'Insufficient stock for the requested movement.');
            }

            $product->update(['current_stock' => $stockAfter]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'movement_type' => $validated['movement_type'],
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
            ]);
        });

        return back()->with('success', 'Inventory movement recorded.');
    }
}
