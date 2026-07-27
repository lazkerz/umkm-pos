<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class OwnerLabaRugiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $storeIds, private $start, private $end) {}

    public function collection(): Collection
    {
        $revenueByStore = Transaction::whereIn('store_id', $this->storeIds)
            ->completed()
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('store_id')
            ->select('store_id', DB::raw('SUM(total) as revenue'))
            ->pluck('revenue', 'store_id');

        $expenseByStore = Expense::whereIn('store_id', $this->storeIds)
            ->approved()
            ->whereBetween('expense_date', [$this->start, $this->end])
            ->groupBy('store_id')
            ->select('store_id', DB::raw('SUM(amount) as total_expense'))
            ->pluck('total_expense', 'store_id');

        return Store::whereIn('id', $this->storeIds)->get()->map(function ($store) use ($revenueByStore, $expenseByStore) {
            $revenue = (float) ($revenueByStore[$store->id] ?? 0);
            $expense = (float) ($expenseByStore[$store->id] ?? 0);

            return (object) [
                'store_name' => $store->name,
                'revenue' => $revenue,
                'expense' => $expense,
                'profit' => $revenue - $expense,
            ];
        });
    }

    public function headings(): array
    {
        return ['Toko', 'Total Pendapatan', 'Total Pengeluaran', 'Laba / Rugi'];
    }

    public function map($row): array
    {
        return [$row->store_name, $row->revenue, $row->expense, $row->profit];
    }

    public function title(): string
    {
        return 'Laba Rugi Semua Toko';
    }
}
