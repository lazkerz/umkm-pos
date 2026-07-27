<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // List satuan yang bisa dipakai toko ini: default global + custom milik toko sendiri
    public function index(Store $store)
    {
        $defaultUnits = Unit::whereNull('store_id')->orderBy('name')->get();
        $customUnits = Unit::where('store_id', $store->id)->orderBy('name')->get();

        return view('store.units.index', compact('store', 'defaultUnits', 'customUnits'));
    }

    // Toko bikin satuan custom sendiri (misal: "Scoop", "Shot", "Cup 250ml")
    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:20'],
        ]);

        $unit = Unit::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
        ]);

        return back()->with('success', "Satuan custom \"{$unit->name}\" berhasil dibuat.");
    }

    public function destroy(Store $store, Unit $unit)
    {
        abort_unless($unit->store_id === $store->id, 403, 'Satuan default tidak bisa dihapus, hanya bisa dihapus kalau ini satuan custom milik toko sendiri.');

        // Cek dulu apakah masih dipakai stock_items
        if ($unit->stockItems()->exists()) {
            return back()->withErrors(['unit' => 'Satuan ini masih dipakai oleh item stok, tidak bisa dihapus.']);
        }

        $unit->delete();

        return back()->with('success', 'Satuan custom berhasil dihapus.');
    }
}
