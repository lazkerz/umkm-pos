<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnDelete();
            // Berapa banyak stock_item terpakai untuk 1 unit product terjual
            // Contoh: Es Kopi Susu -> Biji Kopi 18 gram, Susu UHT 100 ml, Cup 16oz 1 pcs
            $table->decimal('quantity_needed', 12, 3);
            $table->timestamps();

            $table->unique(['product_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_recipes');
    }
};
