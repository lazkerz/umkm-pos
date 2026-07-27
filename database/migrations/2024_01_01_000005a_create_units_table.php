<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            // store_id null = default/global unit (kg, gram, liter, pcs, dll), bisa dipakai semua toko
            // store_id diisi = custom unit yang dibuat toko itu sendiri
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->string('name');   // misal: Kilogram, Gram, Liter, Pcs, Sachet
            $table->string('symbol'); // misal: kg, g, L, pcs, sct
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
