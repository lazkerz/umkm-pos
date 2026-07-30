<?php

namespace App\Http\Controllers\Store;

use App\Exports\LabaRugiExport;
use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Store;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    
    public function index(Request $request, Store $store)
    {
        [$start, $end, $periodLabel, $period] = $this->resolvePeriod($request);

        $totalRevenue = Transaction::where('store_id', $store->id)
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $totalExpense = Expense::where('store_id', $store->id)
            ->approved()
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $totalTransactions = Transaction::where('store_id', $store->id)
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return view('store.reports.index', compact(
            'store', 'period', 'periodLabel', 'totalRevenue', 'totalExpense', 'totalTransactions'
        ));
    }

    public function exportLabaRugiPdf(Request $request, Store $store)
    {
        [$start, $end, $periodLabel] = $this->resolvePeriod($request);

        $transactions = Transaction::where('store_id', $store->id)
            ->completed()
            ->with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $expenses = Expense::where('store_id', $store->id)
            ->approved()
            ->whereBetween('expense_date', [$start, $end])
            ->latest('expense_date')
            ->get();

        $totalRevenue = $transactions->sum('total');
        $totalExpense = $expenses->sum('amount');

        $pdf = Pdf::loadView('pdf.laba-rugi', [
            'store' => $store,
            'periodLabel' => $periodLabel,
            'transactions' => $transactions,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'totalProfit' => $totalRevenue - $totalExpense,
        ]);

        return $pdf->download("laba-rugi-{$store->id}-{$periodLabel}.pdf");
    }

    public function exportLabaRugiExcel(Request $request, Store $store)
    {
        [$start, $end, $periodLabel] = $this->resolvePeriod($request);

        return Excel::download(
            new LabaRugiExport($store, $start, $end, $periodLabel),
            "laba-rugi-{$store->id}-{$periodLabel}.xlsx"
        );
    }

    public function exportSalesPdf(Request $request, Store $store)
    {
        [$start, $end, $periodLabel] = $this->resolvePeriod($request);

        $transactions = Transaction::where('store_id', $store->id)
            ->completed()
            ->with(['customer', 'items.product'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.sales-report', [
            'store' => $store,
            'periodLabel' => $periodLabel,
            'transactions' => $transactions,
        ]);

        return $pdf->download("penjualan-{$store->id}-{$periodLabel}.pdf");
    }

    public function exportSalesExcel(Request $request, Store $store)
    {
        [$start, $end, $periodLabel] = $this->resolvePeriod($request);

        return Excel::download(
            new SalesReportExport($store, $start, $end),
            "penjualan-{$store->id}-{$periodLabel}.xlsx"
        );
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', 'this_month');

        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                \Carbon\Carbon::parse($request->input('start_date', now()->startOfMonth()->toDateString()))->startOfDay(),
                \Carbon\Carbon::parse($request->input('end_date', now()->endOfMonth()->toDateString()))->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $periodLabel = $start->format('d-M-Y') . '_sd_' . $end->format('d-M-Y');

        return [$start, $end, $periodLabel, $period];
    }
}
