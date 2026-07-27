<?php

namespace App\Exports;

use App\Exports\Sheets\PendapatanSheet;
use App\Exports\Sheets\PengeluaranSheet;
use App\Exports\Sheets\RingkasanSheet;
use App\Models\Expense;
use App\Models\Store;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LabaRugiExport implements WithMultipleSheets
{
    public function __construct(
        private Store $store,
        private $start,
        private $end,
        private string $periodLabel,
    ) {}

    public function sheets(): array
    {
        $transactions = Transaction::where('store_id', $this->store->id)
            ->completed()
            ->with('customer')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->latest()
            ->get();

        $expenses = Expense::where('store_id', $this->store->id)
            ->approved()
            ->with(['creator', 'approver'])
            ->whereBetween('expense_date', [$this->start, $this->end])
            ->latest('expense_date')
            ->get();

        $totalRevenue = $transactions->sum('total');
        $totalExpense = $expenses->sum('amount');

        return [
            new RingkasanSheet($this->store->name, $this->periodLabel, $totalRevenue, $totalExpense, $totalRevenue - $totalExpense),
            new PendapatanSheet($transactions),
            new PengeluaranSheet($expenses),
        ];
    }
}
