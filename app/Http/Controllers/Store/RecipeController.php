<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Store;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    // List resep untuk 1 menu tertentu
    public function index(Store $store, Product $product)
    {
        abort_unless($product->store_id === $store->id, 404);

        $recipes = $product->recipes()->with('stockItem.unit')->get();
        $availableStockItems = $store->stockItems()->with('unit')->orderBy('name')->get();

        return view('store.recipes.index', compact('store', 'product', 'recipes', 'availableStockItems'));
    }

    // Tambah 1 baris resep: menu ini butuh bahan X sebanyak Y per 1 unit terjual
    public function store(Request $request, Store $store, Product $product)
    {
        abort_unless($product->store_id === $store->id, 404);

        $validated = $request->validate([
            'stock_item_id' => ['required', 'exists:stock_items,id'],
            'quantity_needed' => ['required', 'numeric', 'min:0.001'],
        ]);

        // Pastikan stock item memang milik toko yang sama
        $stockItem = $store->stockItems()->findOrFail($validated['stock_item_id']);

        $recipe = ProductRecipe::updateOrCreate(
            ['product_id' => $product->id, 'stock_item_id' => $stockItem->id],
            ['quantity_needed' => $validated['quantity_needed']]
        );

        return back()->with('success', "Resep untuk \"{$product->name}\" berhasil disimpan.");
    }

    public function destroy(Store $store, Product $product, ProductRecipe $recipe)
    {
        abort_unless($product->store_id === $store->id && $recipe->product_id === $product->id, 404);

        $recipe->delete();

        return back()->with('success', 'Bahan baku dihapus dari resep menu ini.');
    }
}
