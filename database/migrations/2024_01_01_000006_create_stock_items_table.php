<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name'); // misal: Biji Kopi Arabica, Susu UHT, Cup 16oz
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete(); // pilih dari default atau custom
            $table->decimal('quantity', 12, 2)->default(0); // stok saat ini (running total)
            $table->decimal('minimum_stock', 12, 2)->default(0); // untuk alert stok menipis
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
