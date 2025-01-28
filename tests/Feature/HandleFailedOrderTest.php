<?php

namespace Tests\Feature;

use App\Constants\SupportedPaymentMethods;
use App\Constants\SupportedShippingMethods;
use App\Data\Dto\SSLCommerz\InstantPaymentNotificationData;
use App\Http\Integrations\SSLCommerz\Requests\InitiatePaymentRequest;
use App\Http\Integrations\SSLCommerz\Requests\OrderValidationRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\ProductVariant;
use Saloon;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

class HandleFailedOrderTest extends TestCase
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
        $vendor->deliveryProviderAccounts()->create([
            'provider_name' => SupportedShippingMethods::PATHAO->value,
            'data' => [
                'store_id' => $this->faker->randomNumber(),
            ],
        ]);

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
            'title' => $this->faker->title(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => $this->faker->company(),
            'line_one' => $this->faker->address(),
            'line_two' => $this->faker->address(),
            'line_three' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->name(),
            'postcode' => 'H0H 0H0',
            'delivery_instructions' => null,
            'contact_email' => $this->faker->email(),
            'contact_phone' => $this->faker->phoneNumber(),
            'meta' => null,
            'shipping_option' => SupportedShippingMethods::PATHAO->value,
        ]);

        $cart->setBillingAddress([
            'country_id' => $countryBdId,
            'title' => $this->faker->title(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company_name' => $this->faker->company(),
            'line_one' => $this->faker->address(),
            'line_two' => $this->faker->address(),
            'line_three' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->name(),
            'postcode' => 'H0H 0H0',
            'delivery_instructions' => null,
            'contact_email' => $this->faker->email(),
            'contact_phone' => $this->faker->phoneNumber(),
            'meta' => null,
        ]);

        $customer = Customer::factory()->create();
        $cart->update([
            'customer_id' => $customer->getKey(),
        ]);

        $order = $cart->createOrder();

        return $order;
    }

    public function test_seller_can_change_to_offline_order_for_his_failed_or_cancelled_order(): void
    {
        /** @var User */
        $seller = User::factory()->create();
        Sanctum::actingAs($seller);
        $order = $this->createOrder($seller);

        Saloon::fake([
            InitiatePaymentRequest::class => MockResponse::make(body: [
                'status' => 'failed',
                'failedreason' => 'user cancelled the payment',
                'sessionkey' => '',
                'gw' => [],
                'GatewayPageURL' => $this->faker->url(),
                'storeBanner' => '',
                'storeLogo' => '',
                'desc' => [],
            ], status: 200),

            OrderValidationRequest::class => MockResponse::make(body: [
                'status' => $status = 'failed',
                'tran_date' => $tranDate = $this->faker->dateTime()->format('Y-m-d'),
                'tran_id' => $tranId = $order->cart->getKey(),
                'val_id' => $valId = $this->faker->randomAscii(),
                'amount' => $amount = $order->cart->total->decimal(),
                'store_amount' => $storeAmount = $order->cart->total->decimal(),
                'card_type' => $cartType = $this->faker->creditCardType(),
                'card_no' => $cardNo = $this->faker->creditCardNumber(),
                'currency' => $currency = $this->faker->currencyCode(),
                'bank_tran_id' => $bankTranId = $order->cart->getKey(),
                'card_issuer' => $cardIssuer = $this->faker->company(),
                'card_brand' => $cardBrand = $this->faker->company(),
                'card_issuer_country' => $cardIssuerCountry = $this->faker->country(),
                'card_issuer_country_code' => $cardIssuerCountryCode = $this->faker->countryCode(),
                'currency_type' => $currencyType = $order->cart->currency->code,
                'currency_amount' => $currencyAmount = $order->cart->total->decimal(),
                'value_a' => '',
                'value_b' => '',
                'value_c' => '',
                'value_d' => '',
                'risk_level' => '',
                'risk_title' => '',
            ], status: 200),
        ]);

        $driver = Payments::driver(SupportedPaymentMethods::SSLCOMMERZ->value);
        $data = InstantPaymentNotificationData::from([
            'status' => $status,
            'tran_date' => $tranDate,
            'tran_id' => $tranId,
            'val_id' => $valId,
            'amount' => $amount,
            'store_amount' => $storeAmount,
            'card_type' => $cartType,
            'card_no' => $cardNo,
            'currency' => $currency,
            'bank_tran_id' => $bankTranId,
            'card_issuer' => $cardIssuer,
            'card_brand' => $cardBrand,
            'card_issuer_country' => $cardIssuerCountry,
            'card_issuer_country_code' => $cardIssuerCountryCode,
            'currency_type' => $currencyType,
            'currency_amount' => $currencyAmount,
            'value_a' => '',
            'value_b' => '',
            'value_c' => '',
            'value_d' => '',
            'risk_level' => '',
            'risk_title' => '',
        ]);

        $driver->cart($order->cart)
            ->authorize();

        $driver->cart($order->cart)
            ->withData([
                'ipn_data' => $data->toArray(),
            ])
            ->capture($order->intents->first(), $amount);

        $response = $this->putJson(route('orders.handle-failure', $order->getKey()));

        $order->refresh();

        $response->assertOk()->assertJson([
            'status' => 'payment-offline',
            'placed_at' => $order->placed_at->format(DATE_ATOM),
        ]);

        $this->assertNotNull($order->placed_at);

        $this->assertCount(0, $order->transactions);
    }
}
