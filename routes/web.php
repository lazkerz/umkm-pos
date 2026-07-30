<?php

use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\ExpenseApprovalController;
use App\Http\Controllers\Owner\ReportController as OwnerReportController;
use App\Http\Controllers\Owner\StaffController;
use App\Http\Controllers\Owner\StockDistributionController;
use App\Http\Controllers\Owner\StoreController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Store\CategoryController;
use App\Http\Controllers\Store\CustomerController;
use App\Http\Controllers\Store\DashboardController as StoreDashboardController;
use App\Http\Controllers\Store\ExpenseController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\PromotionController;
use App\Http\Controllers\Store\RecipeController;
use App\Http\Controllers\Store\ReportController;
use App\Http\Controllers\Store\StockItemController;
use App\Http\Controllers\Store\TransactionController;
use App\Http\Controllers\Store\UnitController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $user = auth()->user();

    return $user->isOwner()
        ? redirect()->route('owner.dashboard')
        : redirect()->route('stores.dashboard', $user->store_id);
})->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware('auth')->group(function () {

    
    Route::middleware('owner')->prefix('owner')->name('owner.')->group(function () {

        
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

        
        Route::get('/reports/laba-rugi/pdf', [OwnerReportController::class, 'exportLabaRugiPdf'])->name('reports.laba-rugi.pdf');
        Route::get('/reports/laba-rugi/excel', [OwnerReportController::class, 'exportLabaRugiExcel'])->name('reports.laba-rugi.excel');

        
        Route::resource('stores', StoreController::class)->except(['show']);

        
        
        Route::middleware('store.access')->prefix('stores/{store}')->name('stores.')->group(function () {

            
            Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
            Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
            Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('/staff/{staffMember}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('/staff/{staffMember}', [StaffController::class, 'destroy'])->name('staff.destroy');

            
            Route::get('/stock-distributions', [StockDistributionController::class, 'index'])->name('stock-distributions.index');
            Route::get('/stock-distributions/create', [StockDistributionController::class, 'create'])->name('stock-distributions.create');
            Route::post('/stock-distributions', [StockDistributionController::class, 'store'])->name('stock-distributions.store');

            
            Route::get('/expenses/approval', [ExpenseApprovalController::class, 'index'])->name('expenses.approval');
            Route::post('/expenses/{expense}/approve', [ExpenseApprovalController::class, 'approve'])->name('expenses.approve');
            Route::post('/expenses/{expense}/reject', [ExpenseApprovalController::class, 'reject'])->name('expenses.reject');
        });
    });

    
    Route::middleware('store.access')->prefix('stores/{store}')->name('stores.')->group(function () {

        
        Route::get('/dashboard', [StoreDashboardController::class, 'index'])->name('dashboard');

        
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/laba-rugi/pdf', [ReportController::class, 'exportLabaRugiPdf'])->name('reports.laba-rugi.pdf');
        Route::get('/reports/laba-rugi/excel', [ReportController::class, 'exportLabaRugiExcel'])->name('reports.laba-rugi.excel');
        Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/sales/excel', [ReportController::class, 'exportSalesExcel'])->name('reports.sales.excel');

        
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        
        Route::get('/products/{product}/recipes', [RecipeController::class, 'index'])->name('products.recipes.index');
        Route::post('/products/{product}/recipes', [RecipeController::class, 'store'])->name('products.recipes.store');
        Route::delete('/products/{product}/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('products.recipes.destroy');

        
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        
        Route::get('/stock-items', [StockItemController::class, 'index'])->name('stock-items.index');
        Route::post('/stock-items', [StockItemController::class, 'store'])->name('stock-items.store');
        Route::put('/stock-items/{stockItem}', [StockItemController::class, 'update'])->name('stock-items.update');
        Route::post('/stock-items/{stockItem}/adjust', [StockItemController::class, 'adjustStock'])->name('stock-items.adjust');
        Route::delete('/stock-items/{stockItem}', [StockItemController::class, 'destroy'])->name('stock-items.destroy');

        
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        
        Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
        Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
        Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
        Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');

        
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    });
});


require __DIR__.'/auth.php';