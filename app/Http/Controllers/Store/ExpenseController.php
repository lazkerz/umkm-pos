<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{

    public function index(Store $store)
    {
        $expenses = $store->expenses()->with(['creator', 'approver', 'stockItem'])->latest('expense_date')->paginate(20);
        $stockItems = $store->stockItems()->with('unit')->orderBy('name')->get();

        return view('store.expenses.index', compact('store', 'expenses', 'stockItems'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'expense_date' => ['required', 'date'],
            'stock_item_id' => ['nullable', Rule::exists('stock_items', 'id')->where('store_id', $store->id)],
            'restock_quantity' => ['nullable', 'required_with:stock_item_id', 'numeric', 'min:0.01'],
        ]);

        $user = $request->user();

        $expense = $store->expenses()->create([
            ...$validated,
            'created_by' => $user->id,

            'status' => $user->isOwner() ? 'approved' : 'pending',
            'approved_by' => $user->isOwner() ? $user->id : null,
            'approved_at' => $user->isOwner() ? now() : null,
        ]);

        if ($expense->status === 'approved') {
            $expense->applyRestock($user->id);
        }

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy(Store $store, \App\Models\Expense $expense)
    {
        abort_unless($expense->store_id === $store->id, 404);

        
        abort_unless($expense->status === 'pending', 422, 'Pengeluaran yang sudah diproses tidak bisa dihapus.');

        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
