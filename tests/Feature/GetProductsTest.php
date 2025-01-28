<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class GetProductsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_can_get_product_list(): void
    {
        $currencyId = Currency::getDefault()->getKey();
        Product::factory()->has(
            ProductVariant::factory()
                ->has(
                    Price::factory()->count(2)->state([
                        'currency_id' => $currencyId,
                    ])
                )->count(3),
            'variants'
        )
            ->count(500)
            ->create();

        $response = $this->getJson(route('products'));

        $response->assertOk();
    }
}
