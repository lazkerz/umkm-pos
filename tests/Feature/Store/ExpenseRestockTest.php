<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseRestockTest extends TestCase
{
    use RefreshDatabase;

    private function makeStoreWithStockItem(float $quantity = 10): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);
        $unit = Unit::create(['store_id' => null, 'name' => 'Gram', 'symbol' => 'g']);
        $stockItem = StockItem::create([
            'store_id' => $store->id,
            'name' => 'Bahan Uji',
            'unit_id' => $unit->id,
            'quantity' => $quantity,
            'minimum_stock' => 1,
        ]);

        return compact('owner', 'store', 'stockItem');
    }

    public function test_owner_expense_with_stock_link_restocks_immediately(): void
    {
        ['owner' => $owner, 'store' => $store, 'stockItem' => $stockItem] = $this->makeStoreWithStockItem();

        $this->actingAs($owner)->post(route('stores.expenses.store', $store), [
            'category' => 'Belanja Bahan Baku',
            'amount' => 50000,
            'expense_date' => now()->toDateString(),
            'stock_item_id' => $stockItem->id,
            'restock_quantity' => 5,
        ]);

        $this->assertEquals(15, $stockItem->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }

    public function test_staff_expense_with_stock_link_does_not_restock_until_approved(): void
    {
        ['owner' => $owner, 'store' => $store, 'stockItem' => $stockItem] = $this->makeStoreWithStockItem();
        $staff = User::factory()->create(['role' => 'staff', 'store_id' => $store->id]);

        $this->actingAs($staff)->post(route('stores.expenses.store', $store), [
            'category' => 'Belanja Bahan Baku',
            'amount' => 50000,
            'expense_date' => now()->toDateString(),
            'stock_item_id' => $stockItem->id,
            'restock_quantity' => 5,
        ]);

        // Stok belum berubah karena expense masih pending.
        $this->assertEquals(10, $stockItem->fresh()->quantity);

        $expense = $store->expenses()->first();
        $this->actingAs($owner)->post(route('owner.stores.expenses.approve', [$store, $expense]));

        $this->assertEquals(15, $stockItem->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }

    public function test_expense_without_stock_link_does_not_touch_stock(): void
    {
        ['owner' => $owner, 'store' => $store, 'stockItem' => $stockItem] = $this->makeStoreWithStockItem();

        $this->actingAs($owner)->post(route('stores.expenses.store', $store), [
            'category' => 'Listrik',
            'amount' => 200000,
            'expense_date' => now()->toDateString(),
        ]);

        $this->assertEquals(10, $stockItem->fresh()->quantity);
        $this->assertDatabaseMissing('stock_movements', ['stock_item_id' => $stockItem->id]);
    }
}
