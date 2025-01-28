<?php

namespace Tests\Feature;

use App\Models\Livestream;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LivestreamProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_user_needs_to_be_authenticated_to_add_products_to_livestream(): void
    {
        /** @var Livestream */
        $livestream = Livestream::factory()->create();

        $response = $this->postJson(route('livestream-products.store', $livestream->getKey()), []);

        $response->assertUnauthorized();
    }

    public function test_user_needs_to_be_authenticated_to_remove_products_to_livestream(): void
    {
        /** @var Livestream */
        $livestream = Livestream::factory()->hasProducts(3)->create();

        $response = $this->deleteJson(route('livestream-products.destroy', $livestream->getKey()));

        $response->assertUnauthorized();
    }

    public function test_vendor_owner_needs_to_provide_at_least_one_product_id_to_add_to_livestream()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        /** @var Livestream */
        $livestream = Livestream::factory()->for($vendor)->create();

        $response = $this->postJson(route('livestream-products.store', $livestream->getKey()), [
            'product_ids' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrorFor('product_ids');
    }

    public function test_user_can_not_add_product_to_someone_elses_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Livestream */
        $livestream = Livestream::factory()->create();
        $products = Product::factory()->count(5)->create();

        $productIdsToAdd = $products->skip(2)->take(2)->pluck('id')->toArray();

        $response = $this->postJson(route('livestream-products.store', $livestream->getKey()), [
            'product_ids' => $productIdsToAdd,
        ]);

        $response->assertNotFound();
    }

    public function test_vendor_owner_will_be_able_to_add_products_of_a_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        /** @var Livestream */
        $livestream = Livestream::factory()->for($vendor)->create();
        $products = Product::factory()->count(5)->create();

        $productIdsToAdd = $products->skip(2)->take(2)->pluck('id')->toArray();

        $response = $this->postJson(
            route('livestream-products.store', $livestream->getKey()),
            [
                'product_ids' => $productIdsToAdd,
            ]
        );

        $response->assertCreated();
        $reponseProducts = $response->json('products');

        $livestream->loadCount('products');
        $this->assertEquals(2, $livestream->products_count);
        $this->assertCount(2, $reponseProducts);
    }

    public function test_user_can_not_remove_product_from_someone_elses_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Livestream */
        $livestream = Livestream::factory()->hasProducts(3)->create();
        $productIdsToRemove = $livestream->products->pluck('id')->toArray();

        $response = $this->deleteJson(route('livestream-products.destroy', $livestream->getKey()), [
            'product_ids' => $productIdsToRemove,
        ]);

        $response->assertNotFound();
    }

    public function test_vendor_owner_will_be_able_to_remove_products_from_livestream(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        /** @var Livestream */
        $livestream = Livestream::factory()->for($vendor)->hasProducts(3)->create();
        $productIdsToRemove = $livestream->products->pluck('id')->toArray();

        $response = $this->deleteJson(route('livestream-products.destroy', $livestream->getKey()), [
            'product_ids' => $productIdsToRemove,
        ]);

        $response->assertOk();
        $reponseProducts = $response->json('products');

        $livestream->loadCount('products');
        $this->assertEquals(0, $livestream->products_count);
        $this->assertCount(0, $reponseProducts);
    }
}
