<?php

namespace App\Http\Controllers\Owner;

use App\Exports\OwnerLabaRugiExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Store;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportLabaRugiPdf(Request $request)
    {
        [$storeIds, $start, $end, $periodLabel] = $this->prepare($request);

        $rows = $this->buildRows($storeIds, $start, $end);

        $pdf = Pdf::loadView('pdf.owner-laba-rugi', compact('rows', 'periodLabel'));

        return $pdf->download("laba-rugi-semua-toko-{$periodLabel}.pdf");
    }

    public function exportLabaRugiExcel(Request $request)
    {
        [$storeIds, $start, $end, $periodLabel] = $this->prepare($request);

        return Excel::download(
            new OwnerLabaRugiExport($storeIds, $start, $end),
            "laba-rugi-semua-toko-{$periodLabel}.xlsx"
        );
    }

    private function prepare(Request $request): array
    {
        $storeIds = $request->user()->ownedStores()->pluck('id');
        $period = $request->input('period', 'this_month');

        [$start, $end] = match ($period) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $periodLabel = $start->format('d-M-Y') . '_sd_' . $end->format('d-M-Y');

        return [$storeIds, $start, $end, $periodLabel];
    }

    private function buildRows($storeIds, $start, $end)
    {
        $revenueByStore = Transaction::whereIn('store_id', $storeIds)
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('store_id')
            ->select('store_id', DB::raw('SUM(total) as revenue'))
            ->pluck('revenue', 'store_id');

        $expenseByStore = Expense::whereIn('store_id', $storeIds)
            ->approved()
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('store_id')
            ->select('store_id', DB::raw('SUM(amount) as total_expense'))
            ->pluck('total_expense', 'store_id');

        return Store::whereIn('id', $storeIds)->get()->map(function ($store) use ($revenueByStore, $expenseByStore) {
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
}
