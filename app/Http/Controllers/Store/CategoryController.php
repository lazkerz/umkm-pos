<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Store $store)
    {
        $categories = $store->categories()->withCount('products')->get();

        return view('store.categories.index', compact('store', 'categories'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $store->categories()->create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Store $store, Category $category)
    {
        abort_unless($category->store_id === $store->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(Store $store, Category $category)
    {
        abort_unless($category->store_id === $store->id, 404);

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
