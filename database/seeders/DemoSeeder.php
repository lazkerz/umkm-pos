<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\StockItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        
        $owner = User::firstOrCreate(
            ['email' => 'owner@umkmkopi.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '081234567890',
            ]
        );

        
        $store1 = Store::firstOrCreate(
            ['owner_id' => $owner->id, 'name' => 'Kopi Senja - Cabang Medan'],
            ['address' => 'Jl. Sisingamangaraja No. 10, Medan', 'phone' => '0614512345']
        );

        
        $store2 = Store::firstOrCreate(
            ['owner_id' => $owner->id, 'name' => 'Kopi Senja - Cabang Batam'],
            ['address' => 'Jl. Sudirman No. 5, Batam', 'phone' => '0778112233']
        );

        
        $staff1 = User::firstOrCreate(
            ['email' => 'kasir.medan@umkmkopi.test'],
            [
                'name' => 'Siti Aminah',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'store_id' => $store1->id,
                'phone' => '081298765432',
            ]
        );

        $this->seedStoreData($store1);
        $this->seedStoreData($store2, suffix: ' Batam');

        $this->command->info('Demo data created!');
        $this->command->info('Login Owner: owner@umkmkopi.test / password');
        $this->command->info('Login Staff (Cabang Medan): kasir.medan@umkmkopi.test / password');
    }

    private function seedStoreData(Store $store, string $suffix = ''): void
    {
        
        $shotUnit = Unit::firstOrCreate(
            ['store_id' => $store->id, 'symbol' => 'shot'],
            ['name' => 'Shot Espresso']
        );

        $gramUnit = Unit::where('symbol', 'g')->whereNull('store_id')->first();
        $mlUnit = Unit::where('symbol', 'ml')->whereNull('store_id')->first();
        $pcsUnit = Unit::where('symbol', 'pcs')->whereNull('store_id')->first();

        
        $catKopi = Category::firstOrCreate(['store_id' => $store->id, 'name' => 'Kopi Susu']);
        $catNonKopi = Category::firstOrCreate(['store_id' => $store->id, 'name' => 'Non-Kopi']);

        
        $bijiKopi = StockItem::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Biji Kopi Arabica'],
            ['unit_id' => $gramUnit->id, 'quantity' => 5000, 'minimum_stock' => 1000]
        );

        $susu = StockItem::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Susu UHT'],
            ['unit_id' => $mlUnit->id, 'quantity' => 8000, 'minimum_stock' => 2000]
        );

        $cup = StockItem::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Cup 16oz'],
            ['unit_id' => $pcsUnit->id, 'quantity' => 200, 'minimum_stock' => 50]
        );

        $gulaAren = StockItem::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Gula Aren'],
            ['unit_id' => $mlUnit->id, 'quantity' => 30, 'minimum_stock' => 500] 
        );

        
        $esKopiSusu = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Es Kopi Susu Gula Aren' . $suffix],
            ['category_id' => $catKopi->id, 'price' => 18000, 'description' => 'Kopi susu gula aren khas Kopi Senja']
        );

        $espresso = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Espresso' . $suffix],
            ['category_id' => $catKopi->id, 'price' => 15000, 'description' => 'Espresso shot tunggal']
        );

        $tehLeci = Product::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Teh Leci' . $suffix],
            ['category_id' => $catNonKopi->id, 'price' => 12000]
        );

        
        ProductRecipe::firstOrCreate(
            ['product_id' => $esKopiSusu->id, 'stock_item_id' => $bijiKopi->id],
            ['quantity_needed' => 18]
        );
        ProductRecipe::firstOrCreate(
            ['product_id' => $esKopiSusu->id, 'stock_item_id' => $susu->id],
            ['quantity_needed' => 100]
        );
        ProductRecipe::firstOrCreate(
            ['product_id' => $esKopiSusu->id, 'stock_item_id' => $gulaAren->id],
            ['quantity_needed' => 20]
        );
        ProductRecipe::firstOrCreate(
            ['product_id' => $esKopiSusu->id, 'stock_item_id' => $cup->id],
            ['quantity_needed' => 1]
        );
        ProductRecipe::firstOrCreate(
            ['product_id' => $espresso->id, 'stock_item_id' => $bijiKopi->id],
            ['quantity_needed' => 9]
        );

        
        Promotion::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Promo Buka Cabang'],
            [
                'type' => 'percentage',
                'value' => 10,
                'channel' => 'both',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'is_active' => true,
            ]
        );

        
        $customer = Customer::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Andi Wijaya'],
            ['phone' => '081211112222']
        );

        
        for ($i = 0; $i < 5; $i++) {
            $subtotal = $esKopiSusu->price * 2;
            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . $store->id . '-DEMO-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'store_id' => $store->id,
                'channel' => $i % 2 === 0 ? 'offline' : 'online',
                'customer_id' => $i === 0 ? $customer->id : null,
                'staff_id' => null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'payment_method' => 'cash',
                'status' => 'completed',
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $esKopiSusu->id,
                'quantity' => 2,
                'price' => $esKopiSusu->price,
                'subtotal' => $subtotal,
            ]);
        }

        
        Expense::firstOrCreate(
            ['store_id' => $store->id, 'category' => 'Sewa Tempat', 'expense_date' => now()->startOfMonth()],
            [
                'amount' => 2000000,
                'description' => 'Sewa bulanan',
                'status' => 'approved',
                'created_by' => $store->owner_id,
                'approved_by' => $store->owner_id,
                'approved_at' => now(),
            ]
        );

        Expense::firstOrCreate(
            ['store_id' => $store->id, 'category' => 'Bahan Baku Tambahan', 'expense_date' => now()],
            [
                'amount' => 350000,
                'description' => 'Beli gula aren darurat, stok menipis',
                'status' => 'pending',
                'created_by' => $store->owner_id,
            ]
        );
    }
}
