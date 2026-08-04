<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Opsional: kalau expense ini pembelian bahan baku, kaitkan ke StockItem +
            // jumlah yang dibeli. Stok baru bertambah pas status expense jadi 'approved'
            // (lihat ExpenseController::store() dan Owner\ExpenseApprovalController::approve()).
            $table->foreignId('stock_item_id')->nullable()->after('store_id')->constrained('stock_items')->nullOnDelete();
            $table->decimal('restock_quantity', 10, 2)->nullable()->after('stock_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_item_id');
            $table->dropColumn('restock_quantity');
        });
    }
};
