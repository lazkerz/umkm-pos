<?php

namespace Tests\Feature\Store;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Support\StoreCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TransactionCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_are_cached_after_opening_kasir(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);
        $category = Category::create(['store_id' => $store->id, 'name' => 'Kategori Uji']);
        Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk A',
            'price' => 5000,
            'is_available' => true,
        ]);

        $this->assertFalse(Cache::has("store:{$store->id}:products:available"));

        $this->actingAs($owner)->get(route('stores.transactions.create', $store))->assertOk();

        $this->assertTrue(Cache::has("store:{$store->id}:products:available"));
        $this->assertCount(1, StoreCache::products($store->id));
    }

    public function test_saving_a_product_invalidates_the_cache(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);
        $category = Category::create(['store_id' => $store->id, 'name' => 'Kategori Uji']);

        // Warm the cache with zero products.
        StoreCache::products($store->id);
        $this->assertCount(0, StoreCache::products($store->id));

        Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk Baru',
            'price' => 5000,
            'is_available' => true,
        ]);

        $this->assertFalse(Cache::has("store:{$store->id}:products:available"));
        $this->assertCount(1, StoreCache::products($store->id));
    }

    public function test_deleting_a_product_invalidates_the_cache(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);
        $category = Category::create(['store_id' => $store->id, 'name' => 'Kategori Uji']);
        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk A',
            'price' => 5000,
            'is_available' => true,
        ]);

        $this->assertCount(1, StoreCache::products($store->id));

        $product->delete();

        $this->assertFalse(Cache::has("store:{$store->id}:products:available"));
        $this->assertCount(0, StoreCache::products($store->id));
    }
}
