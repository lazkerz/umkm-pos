<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    
    public function index(Request $request)
    {
        $stores = $request->user()->ownedStores()->withCount('staff')->latest()->get();

        return view('owner.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('owner.stores.create');
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $store = $request->user()->ownedStores()->create($validated);

        return redirect()
            ->route('owner.stores.index')
            ->with('success', "Toko \"{$store->name}\" berhasil dibuat.");
    }

    public function edit(Store $store)
    {
        return view('owner.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $store->update($validated);

        return redirect()
            ->route('owner.stores.index')
            ->with('success', 'Toko berhasil diupdate.');
    }

    public function destroy(Store $store)
    {
        $store->delete();

        return redirect()
            ->route('owner.stores.index')
            ->with('success', 'Toko berhasil dihapus.');
    }
}
