<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,   // satuan default global (kg, gram, liter, dll)
            DemoSeeder::class,   // owner, toko, staff, menu, stok, resep, transaksi dummy
        ]);
    }
}
