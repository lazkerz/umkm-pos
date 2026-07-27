<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Store $store)
    {
        $customers = $store->customers()->withCount('transactions')->latest()->paginate(20);

        return view('store.customers.index', compact('store', 'customers'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $store->customers()->create($validated);

        return back()->with('success', 'Customer berhasil ditambahkan.');
    }

    public function update(Request $request, Store $store, Customer $customer)
    {
        abort_unless($customer->store_id === $store->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Data customer berhasil diupdate.');
    }

    public function destroy(Store $store, Customer $customer)
    {
        abort_unless($customer->store_id === $store->id, 404);

        $customer->delete();

        return back()->with('success', 'Customer berhasil dihapus.');
    }
}
