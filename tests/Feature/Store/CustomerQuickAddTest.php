<?php

namespace Tests\Feature\Store;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerQuickAddTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_json_customer_when_json_requested(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);

        $response = $this->actingAs($owner)->postJson(route('stores.customers.store', $store), [
            'name' => 'Budi',
            'phone' => '08123456789',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'Budi']);
        $this->assertDatabaseHas('customers', ['store_id' => $store->id, 'name' => 'Budi']);
    }

    public function test_store_still_redirects_back_for_non_json_request(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $store = Store::create(['owner_id' => $owner->id, 'name' => 'Toko Uji']);

        $response = $this->actingAs($owner)->post(route('stores.customers.store', $store), [
            'name' => 'Budi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
