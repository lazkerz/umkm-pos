<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // "Input Pengeluaran" - staff/owner input, default status pending kalau staff yang input
    public function index(Store $store)
    {
        $expenses = $store->expenses()->with(['creator', 'approver'])->latest('expense_date')->paginate(20);

        return view('store.expenses.index', compact('store', 'expenses'));
    }

    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'expense_date' => ['required', 'date'],
        ]);

        $user = $request->user();

        $store->expenses()->create([
            ...$validated,
            'created_by' => $user->id,
            // Owner input sendiri langsung auto-approved, staff input -> pending nunggu approval
            'status' => $user->isOwner() ? 'approved' : 'pending',
            'approved_by' => $user->isOwner() ? $user->id : null,
            'approved_at' => $user->isOwner() ? now() : null,
        ]);

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy(Store $store, \App\Models\Expense $expense)
    {
        abort_unless($expense->store_id === $store->id, 404);

        // Cuma bisa hapus kalau masih pending (yang udah approved biarin jadi record histori)
        abort_unless($expense->status === 'pending', 422, 'Pengeluaran yang sudah diproses tidak bisa dihapus.');

        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
