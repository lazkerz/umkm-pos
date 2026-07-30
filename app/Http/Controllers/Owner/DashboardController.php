<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Promotion;
use App\Models\StockItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $owner = $request->user();
        $storeIds = $owner->ownedStores()->pluck('id');

        $period = $request->input('period', 'this_month'); 
        [$start, $end] = $this->resolvePeriod($period, $request);

        $labaRugi = $this->calculateLabaRugi($storeIds, $start, $end);
        $lowStockItems = $this->getLowStockItems($storeIds);
        $storePerformance = $this->getStorePerformance($storeIds, $start, $end);
        $customerSummary = $this->getCustomerSummary($storeIds);
        $promoSummary = $this->getPromoSummary($storeIds);

        return view('owner.dashboard.index', compact(
            'labaRugi',
            'lowStockItems',
            'storePerformance',
            'customerSummary',
            'promoSummary',
            'period',
            'start',
            'end'
        ));
    }

    private function calculateLabaRugi($storeIds, $start, $end): array
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

        $detail = [];
        foreach ($storeIds as $storeId) {
            $revenue = (float) ($revenueByStore[$storeId] ?? 0);
            $expense = (float) ($expenseByStore[$storeId] ?? 0);
            $detail[$storeId] = [
                'revenue' => $revenue,
                'expense' => $expense,
                'profit' => $revenue - $expense,
            ];
        }

        return [
            'per_store' => $detail,
            'total_revenue' => array_sum(array_column($detail, 'revenue')),
            'total_expense' => array_sum(array_column($detail, 'expense')),
            'total_profit' => array_sum(array_column($detail, 'profit')),
        ];
    }

    private function getLowStockItems($storeIds)
    {
        return StockItem::whereIn('store_id', $storeIds)
            ->with(['store', 'unit'])
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->orderBy('quantity')
            ->get();
    }

    /**
     * STORE PERFORMANCE - ranking toko berdasarkan omzet & jumlah transaksi
     */
    private function getStorePerformance($storeIds, $start, $end)
    {
        return Transaction::whereIn('store_id', $storeIds)
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->with('store')
            ->groupBy('store_id')
            ->select(
                'store_id',
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('AVG(total) as avg_transaction_value')
            )
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * CUSTOMER MANAGEMENT - ringkasan jumlah customer per toko
     */
    private function getCustomerSummary($storeIds)
    {
        return DB::table('customers')
            ->whereIn('store_id', $storeIds)
            ->groupBy('store_id')
            ->select('store_id', DB::raw('COUNT(*) as total_customers'))
            ->get();
    }

    /**
     * PROMO MANAGEMENT - ringkasan promo aktif per toko
     */
    private function getPromoSummary($storeIds)
    {
        return Promotion::whereIn('store_id', $storeIds)
            ->with('store')
            ->where('is_active', true)
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('end_date')
            ->get();
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        return match ($period) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $request->input('start_date', now()->startOfMonth()->toDateString()),
                $request->input('end_date', now()->endOfMonth()->toDateString()),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()], 
        };
    }
}
