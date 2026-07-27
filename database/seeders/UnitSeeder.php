<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Liter', 'symbol' => 'L'],
            ['name' => 'Mililiter', 'symbol' => 'ml'],
            ['name' => 'Pieces', 'symbol' => 'pcs'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Pack', 'symbol' => 'pack'],
            ['name' => 'Sachet', 'symbol' => 'sct'],
            ['name' => 'Botol', 'symbol' => 'btl'],
            ['name' => 'Karton', 'symbol' => 'ktn'],
        ];

        foreach ($defaults as $unit) {
            Unit::firstOrCreate(
                ['symbol' => $unit['symbol'], 'store_id' => null],
                ['name' => $unit['name']]
            );
        }
    }
}
