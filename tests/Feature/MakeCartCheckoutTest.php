<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class MakeCartCheckoutTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_need_shipping_and_billing_address_for_checkout(): void
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

        $cart->addLines($products->map(fn (Product $prod) => [
            'quantity' => 1,
            'purchasable' => $prod->variants->first(),
        ]));

        $response = $this->postJson(route('carts.checkout', $cart->getKey()), []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shipping_address', 'billing_address']);
    }

    public function test_need_customer_info_for_checkout(): void
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

        $cart->addLines($products->map(fn (Product $prod) => [
            'quantity' => 1,
            'purchasable' => $prod->variants->first(),
        ]));

        $response = $this->postJson(route('carts.checkout', $cart->getKey()), []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('customer');
    }

    public function test_creates_draft_order_after_checkout(): void
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

        $cart->addLines($products->map(fn (Product $prod) => [
            'quantity' => 1,
            'purchasable' => $prod->variants->first(),
        ]));

        $response = $this->postJson(route('carts.checkout', $cart->getKey()), [
            'customer' => [
                'last_name' => $lName = $this->faker->lastName('male'),
                'first_name' => $fName = $this->faker->firstName('male'),
            ],
            'shipping_address' => [
                'title' => $shptitle = $this->faker->title(),
                'first_name' => $shpfirstName = $this->faker->firstName(),
                'last_name' => $shplastName = $this->faker->lastName(),
                'company_name' => $shpcompanyName = $this->faker->company(),
                'line_one' => $shplineOne = $this->faker->address(),
                'line_two' => $shplineTwo = $this->faker->address(),
                'line_three' => $shplineThree = $this->faker->address(),
                'city' => $shpcity = $this->faker->city(),
                'state' => $shpstate = $this->faker->address(),
                'postcode' => $shppostcode = $this->faker->postcode(),
                'delivery_instructions' => $shpdeliveryInstructions = $this->faker->sentence(),
                'contact_email' => $shpcontactEmail = $this->faker->companyEmail(),
                'contact_phone' => $shpcontactPhone = $this->faker->phoneNumber(),
                'shipping_default' => $shpshippingDefault = true,
                'billing_default' => $shpbillingDefault = false,
            ],
            'billing_address' => [
                'title' => $btitle = $this->faker->title(),
                'first_name' => $bfirstName = $this->faker->firstName(),
                'last_name' => $blastName = $this->faker->lastName(),
                'company_name' => $bcompanyName = $this->faker->company(),
                'line_one' => $blineOne = $this->faker->address(),
                'line_two' => $blineTwo = $this->faker->address(),
                'line_three' => $blineThree = $this->faker->address(),
                'city' => $bcity = $this->faker->city(),
                'state' => $bstate = $this->faker->address(),
                'postcode' => $bpostcode = $this->faker->postcode(),
                'delivery_instructions' => $bdeliveryInstructions = $this->faker->sentence(),
                'contact_email' => $bcontactEmail = $this->faker->companyEmail(),
                'contact_phone' => $bcontactPhone = $this->faker->phoneNumber(),
                'shipping_default' => $bshippingDefault = false,
                'billing_default' => $bbillingDefault = true,
            ],
        ]);

        $cart->loadCount('addresses');
        $this->assertEquals($cart->addresses_count, 2);

        $this->assertNotNull($cart->draftOrder);

        $response
            ->assertCreated();
    }
}
