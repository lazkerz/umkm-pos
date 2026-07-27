<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Store;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Store $store)
    {
        $promotions = $store->promotions()->latest()->get();

        return view('store.promotions.index', compact('store', 'promotions'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'channel' => ['required', 'in:offline,online,both'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $store->promotions()->create($validated);

        return back()->with('success', 'Promo berhasil dibuat.');
    }

    public function update(Request $request, Store $store, Promotion $promotion)
    {
        abort_unless($promotion->store_id === $store->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'channel' => ['required', 'in:offline,online,both'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $promotion->update($validated);

        return back()->with('success', 'Promo berhasil diupdate.');
    }

    public function destroy(Store $store, Promotion $promotion)
    {
        abort_unless($promotion->store_id === $store->id, 404);

        $promotion->delete();

        return back()->with('success', 'Promo berhasil dihapus.');
    }
}
