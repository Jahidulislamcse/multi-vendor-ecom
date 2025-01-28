<?php

namespace Tests\Feature;

use App\Constants\SupportedPaymentMethods;
use App\Constants\SupportedShippingMethods;
use App\Data\Dto\SSLCommerz\InitiatePaymentResponseData;
use App\Http\Integrations\SSLCommerz\Requests\InitiatePaymentRequest;
use App\Models\Product;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Saloon;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

class InitialtePaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_after_initiating_sslcommerz_payment_will_get_gateway_page_url(): void
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

        $customer = Customer::factory()->create();
        $cart->setCustomer($customer);
        $countryId = Country::whereIso2('BD')->first()->getKey();
        $shippingAddress = Address::factory()->create([
            'country_id' => $countryId,
            'postcode' => $this->faker->postcode(),
            'contact_email' => $this->faker->email,
            'contact_phone' => $this->faker->phoneNumber(),
        ]);
        $billingAddress = Address::factory()->create([
            'country_id' => $countryId,
            'postcode' => $this->faker->postcode(),
        ]);
        $cart->setShippingAddress($shippingAddress);
        $cart->setBillingAddress($billingAddress);
        $cart->shippingAddress()->update([
            'contact_email' => $this->faker->email,
            'contact_phone' => $this->faker->phoneNumber(),
            'shipping_option' => SupportedShippingMethods::PATHAO->value,
        ]);

        $taxClass = TaxClass::getDefault();

        $cart->setShippingOption(new ShippingOption(
            taxClass: $taxClass,
            name: 'Pathao',
            description: 'Delivery with pathao',
            price: new Price(0, $cart->currency, 1),
            identifier: SupportedShippingMethods::PATHAO->value,
        ));
        $cart->addLines($products->map(fn (Product $prod) => [
            'purchasable' => $prod->variants->first(),
            'quantity' => 1,
        ]));

        $mockInitiateResponse = new InitiatePaymentResponseData(
            status: 'SUCCESS',
            failedreason: '',
            sessionkey: $sessionKey = $this->faker->sha1(),
            gw: [],
            GatewayPageURL: $gatewayUrl = $this->faker->url(),
            storeBanner: $this->faker->imageUrl(),
            storeLogo: $this->faker->imageUrl(),
            desc: []
        );

        $mockClient = Saloon::fake([
            InitiatePaymentRequest::class => MockResponse::make($mockInitiateResponse->toArray()),
        ]);

        $response = $this->postJson(route('payments.initiate', [$cart->getKey(), SupportedPaymentMethods::SSLCOMMERZ->value]), [

        ]);

        $response->assertCreated()
            ->assertJson([
                'GatewayPageURL' => $gatewayUrl,
            ]);
        $mockClient->assertSent(InitiatePaymentRequest::class);

        $this->assertDatabaseHas(Cart::getModel()->getTable(), [
            'meta->sessionkey' => $sessionKey,
        ]);
    }
}
