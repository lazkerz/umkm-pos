<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard per toko (yang muncul waktu buka Store: Menu > Dashboard):
     * - Data Customer
     * - Report Penjualan
     */
    public function index(Request $request, Store $store)
    {
        $period = $request->input('period', 'this_month');
        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        // Report Penjualan
        $salesReport = $store->transactions()
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                'channel',
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(*) as total_transactions')
            )
            ->groupBy('date', 'channel')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->where('transactions.store_id', $store->id)
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name')
            ->select('products.name', DB::raw('SUM(transaction_items.quantity) as total_sold'))
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        // Data Customer
        $totalCustomers = $store->customers()->count();
        $newCustomersThisPeriod = $store->customers()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $topCustomers = $store->customers()
            ->withCount(['transactions' => function ($q) use ($start, $end) {
                $q->completed()->whereBetween('created_at', [$start, $end]);
            }])
            ->withSum(['transactions' => function ($q) use ($start, $end) {
                $q->completed()->whereBetween('created_at', [$start, $end]);
            }], 'total')
            ->orderByDesc('transactions_sum_total')
            ->limit(10)
            ->get();

        return view('store.dashboard.index', compact(
            'store',
            'salesReport',
            'topProducts',
            'totalCustomers',
            'newCustomersThisPeriod',
            'topCustomers',
            'period'
        ));
    }
}
