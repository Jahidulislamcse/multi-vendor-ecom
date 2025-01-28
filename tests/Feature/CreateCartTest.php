<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class CreateCartTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_after_adding_a_cart_item_without_any_id_in_payload_it_will_create_a_cart(): void
    {

        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        $response = $this->postJson(route('carts.store'), [
            'products' => $products->map(fn (Product $product) => [
                'quantity' => 1,
                'id' => $product->getKey(),
            ]),
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'lines' => [
                    '*' => [
                        'id',
                        'product_variant' => [
                            'id',
                            'sku',
                            'unit_quantity',
                            'product' => [
                                'attribute_data' => [
                                    'name',
                                    'description',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);
    }

    public function test_after_adding_a_cart_item_cart_id_in_payload_it_will_not_create_new_cart(): void
    {

        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        $cart = Cart::create([
            'channel_id' => Channel::getDefault()->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]);

        $response = $this->postJson(route('carts.store'), [
            'cart_id' => $cart->getKey(),
            'products' => $products->map(fn (Product $product) => [
                'quantity' => 1,
                'id' => $product->getKey(),
            ]),
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'lines' => [
                    '*' => [
                        'id',
                        'product_variant' => [
                            'id',
                            'sku',
                            'unit_quantity',
                            'product' => [
                                'attribute_data' => [
                                    'name',
                                    'description',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);
    }

    public function test_update_cart_line_quantity(): void
    {

        /** @var Collection<int, Product> */
        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        /** @var Cart */
        $cart = Cart::create([
            'channel_id' => Channel::getDefault()->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]);

        $cart->addLines($products->map(fn (Product $prod) => [
            'purchasable' => $prod->variants->first(),
            'quantity' => 1,
        ]));

        $response = $this->putJson(route('carts.update', $cart->getKey()), [
            'cartlines' => $products->map(fn (Product $prod) => [
                'id' => $prod->getKey(),
                'quantity' => 3,
            ]),
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'lines' => [
                    '0' => [
                        'quantity' => 3,
                    ],
                ],
            ]);

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);
    }

    public function test_remove_cart_line_item(): void
    {

        /** @var Collection<int, Product> */
        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        /** @var Cart */
        $cart = Cart::create([
            'channel_id' => Channel::getDefault()->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]);

        $cart->addLines($products->map(fn (Product $prod) => [
            'purchasable' => $prod->variants->first(),
            'quantity' => 1,
        ]));

        /** @var CartLine */
        $cartLineToRemove = $cart->lines->first();

        $response = $this->deleteJson(route('carts.destroy', $cart->getKey()), [
            'cartline_id' => $cartLineToRemove->getKey(),
        ]);

        $response->assertJsonCount(2, 'lines');

        $response
            ->assertOk();

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);
    }

    public function test_adding_already_added_product_id_to_the_cart_will_increase_quantity(): void
    {

        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        /** @var Cart */
        $cart = Cart::create([
            'channel_id' => Channel::getDefault()->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]);

        $cart->addLines([
            [
                'quantity' => 1,
                'purchasable' => $products->first()->variants->first(),
            ],
        ]);

        $response = $this->postJson(route('carts.store'), [
            'cart_id' => $cart->getKey(),
            'products' => $products->map(fn (Product $product) => [
                'quantity' => 1,
                'id' => $product->getKey(),
            ]),
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'lines' => [
                    '0' => [
                        'quantity' => 2,
                    ],
                ],
            ]);

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);
    }

    public function test_auth_user_creating_cart_will_store_his_reference(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        $response = $this->postJson(route('carts.store'), [
            'products' => $products->map(fn (Product $product) => [
                'quantity' => 1,
                'id' => $product->getKey(),
            ]),
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'user' => [
                    'id' => $user->getKey(),
                ],
            ]);

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);
    }

    public function test_cart_created_as_guest_will_be_attached_to_user_if_later_logs_in(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()
            ->has(
                ProductVariant::factory()
                    ->hasPrices(1, [
                        'currency_id' => Currency::getDefault()->getKey(),
                    ]),
                'variants'
            )
            ->count(3)
            ->create();

        /** @var Cart */
        $cart = Cart::create([
            'channel_id' => Channel::getDefault()->getKey(),
            'currency_id' => Currency::getDefault()->getKey(),
        ]);

        $cart->addLines([
            [
                'quantity' => 1,
                'purchasable' => $products->first()->variants->first(),
            ],
        ]);

        $response = $this->postJson(route('carts.store'), [
            'cart_id' => $cart->getKey(),
            'products' => $products->map(fn (Product $product) => [
                'quantity' => 1,
                'id' => $product->getKey(),
            ]),
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'user' => [
                    'id' => $user->getKey(),
                ],
            ]);

        $this->assertDatabaseCount(Cart::getModel()->getTable(), 1);

        $response = $this->getJson(route('auth.active-cart'));

        $this->assertNotNull($response->json('lines.0.unit_price'));
        $this->assertNotNull($response->json('lines.0.total'));
        $this->assertNotNull($response->json('lines.0.sub_total'));
    }
}
