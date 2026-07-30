<?php

namespace Tests\Feature\Store;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Store;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeStoreWithRecipeProduct(float $stockQuantity, float $quantityNeeded): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);
        $unit = Unit::create(['store_id' => null, 'name' => 'Gram', 'symbol' => 'g']);
        $category = Category::create(['store_id' => $store->id, 'name' => 'Kategori Uji']);

        $stockItem = StockItem::create([
            'store_id' => $store->id,
            'name' => 'Bahan Uji',
            'unit_id' => $unit->id,
            'quantity' => $stockQuantity,
            'minimum_stock' => 1,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk Uji',
            'price' => 10000,
            'is_available' => true,
        ]);

        ProductRecipe::create([
            'product_id' => $product->id,
            'stock_item_id' => $stockItem->id,
            'quantity_needed' => $quantityNeeded,
        ]);

        return compact('owner', 'store', 'product', 'stockItem');
    }

    public function test_successful_checkout_deducts_stock_via_recipe(): void
    {
        ['owner' => $owner, 'store' => $store, 'product' => $product, 'stockItem' => $stockItem]
            = $this->makeStoreWithRecipeProduct(stockQuantity: 10, quantityNeeded: 2);

        $response = $this->actingAs($owner)->post(route('stores.transactions.store', $store), [
            'channel' => 'offline',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', ['store_id' => $store->id, 'status' => 'completed']);
        $this->assertEquals(8, $stockItem->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'stock_item_id' => $stockItem->id,
            'type' => 'out',
            'quantity' => 2,
        ]);
    }

    public function test_insufficient_stock_fails_validation_without_writing_anything(): void
    {
        ['owner' => $owner, 'store' => $store, 'product' => $product, 'stockItem' => $stockItem]
            = $this->makeStoreWithRecipeProduct(stockQuantity: 1, quantityNeeded: 2);

        $response = $this->actingAs($owner)->post(route('stores.transactions.store', $store), [
            'channel' => 'offline',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('transactions', ['store_id' => $store->id]);
        $this->assertEquals(1, $stockItem->fresh()->quantity);
    }

    public function test_insufficient_stock_returns_422_json_without_wiping_cart_state(): void
    {
        ['owner' => $owner, 'store' => $store, 'product' => $product]
            = $this->makeStoreWithRecipeProduct(stockQuantity: 1, quantityNeeded: 2);

        $response = $this->actingAs($owner)->postJson(route('stores.transactions.store', $store), [
            'channel' => 'offline',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    public function test_successful_checkout_via_json_returns_redirect_url(): void
    {
        ['owner' => $owner, 'store' => $store, 'product' => $product]
            = $this->makeStoreWithRecipeProduct(stockQuantity: 10, quantityNeeded: 2);

        $response = $this->actingAs($owner)->postJson(route('stores.transactions.store', $store), [
            'channel' => 'offline',
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['redirect']);
    }
}
