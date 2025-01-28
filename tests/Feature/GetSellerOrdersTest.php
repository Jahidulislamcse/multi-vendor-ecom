<?php

namespace Tests\Feature;

use App\Constants\SupportedShippingMethods;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class GetSellerOrdersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    private function createOrder(User $seller, ?User $buyer = null)
    {
        $buyer = $buyer ?: User::factory()->create();
        $vendor = Vendor::factory()->for($seller)->create();
        $products = Product::factory()->for($vendor)->count(3)->create();

        $products->each(function (Product $product) {
            $variant = ProductVariant::factory()->for($product)->create();
            $variant->basePrices()->create([
                'price' => 1000,
                'currency_id' => Currency::getDefault()->getKey(),
            ]);
        });

        /** @var Cart */
        $cart = Cart::factory()->create([
            'user_id' => $buyer->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]);

        $cart->addLines($products->map(fn (Product $product) => [
            'quantity' => 1,
            'purchasable' => $product->variants->first(),
        ]));

        $countryBdId = Country::whereIso2('BD')->first()->getKey();

        $cart->setShippingAddress([
            'country_id' => $countryBdId,
            'title' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => null,
            'company_name' => null,
            'line_one' => $this->faker->address(),
            'line_two' => null,
            'line_three' => null,
            'city' => $this->faker->city(),
            'state' => null,
            'postcode' => 'H0H 0H0',
            'delivery_instructions' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'meta' => null,
            'shipping_option' => SupportedShippingMethods::PATHAO->value,
        ]);

        $cart->setBillingAddress([
            'country_id' => $countryBdId,
            'title' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => null,
            'company_name' => null,
            'line_one' => $this->faker->address(),
            'line_two' => null,
            'line_three' => null,
            'city' => $this->faker->city(),
            'state' => null,
            'postcode' => 'H0H 0H0',
            'delivery_instructions' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'meta' => null,
        ]);

        return $cart->createOrder();
    }

    public function test_seller_can_see_all_the_orders_for_his_vendor(): void
    {
        /** @var User */
        $seller = User::factory()->create();
        Sanctum::actingAs($seller);
        /** @var User */
        $seller2 = User::factory()->create();

        $this->createOrder($seller);
        $this->createOrder($seller2);

        $response = $this->getJson(route('orders.index', [
            'filter' => [
                'vendor_id' => $seller->vendors->first()->getKey(),
            ],
        ]));

        $response->assertOk()
            ->assertJson([
                'meta' => [
                    'total' => 1,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reference',
                    ],
                ],
            ]);
    }
}
