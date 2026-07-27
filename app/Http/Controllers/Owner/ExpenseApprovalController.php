<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Store;
use Illuminate\Http\Request;

class ExpenseApprovalController extends Controller
{
    // List pengeluaran yang masih pending untuk toko ini
    public function index(Store $store)
    {
        $pendingExpenses = $store->expenses()
            ->pending()
            ->with('creator')
            ->latest('expense_date')
            ->get();

        $history = $store->expenses()
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['creator', 'approver'])
            ->latest('approved_at')
            ->paginate(20);

        return view('owner.expenses.index', compact('store', 'pendingExpenses', 'history'));
    }

    // "Approve / Input Menu" bagian approve pengeluaran
    public function approve(Request $request, Store $store, Expense $expense)
    {
        abort_unless($expense->store_id === $store->id, 404);

        $expense->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengeluaran disetujui.');
    }

    public function reject(Request $request, Store $store, Expense $expense)
    {
        abort_unless($expense->store_id === $store->id, 404);

        $validated = $request->validate([
            'rejection_note' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'description' => $expense->description . ' | Ditolak: ' . ($validated['rejection_note'] ?? '-'),
        ]);

        return back()->with('success', 'Pengeluaran ditolak.');
    }
}
