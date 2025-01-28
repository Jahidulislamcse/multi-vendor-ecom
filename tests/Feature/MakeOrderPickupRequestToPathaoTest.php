<?php

namespace Tests\Feature;

use App\Constants\SupportedShippingMethods;
use App\Data\Dto\Pathao\CreateNewOrderRequestData;
use App\Data\Dto\Pathao\CreateNewOrderResponseData;
use App\Data\Dto\Pathao\NewOrderData;
use App\Facades\Pathao;
use App\Http\Integrations\Pathao\Requests\GetAccessTokenRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\ProductVariant;
use Saloon;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

use function PHPUnit\Framework\equalTo;

class MakeOrderPickupRequestToPathaoTest extends TestCase
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
            'contact_email' => $this->faker->email(),
            'contact_phone' => $this->faker->phoneNumber(),
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

    public function test_seller_can_make_pickup_request_for_his_placed_order(): void
    {
        /** @var User */
        $seller = User::factory()->create();
        $order = $this->createOrder($seller);

        Sanctum::actingAs($seller);

        $order->update([
            'placed_at' => now(),
        ]);

        $vendor = $seller->vendors->first();

        $recipientCityId = 1;
        $recipientZoneId = 1;
        $recipientAreaId = 1;

        /** @var Price */
        $amountToCollect = $order->total;
        Saloon::fake([
            GetAccessTokenRequest::class => MockResponse::make([
                'access_token' => $this->faker->sha1(),
                'refresh_token' => $this->faker->sha1(),
                'token_type' => 'Bearer',
                'expires_in' => now()->addHours(1)->timestamp,
            ]),
        ]);
        Pathao::shouldReceive('createNewOrder')->once()->withArgs([
            equalTo(new CreateNewOrderRequestData(
                $seller->vendors->first()->deliveryProviderAccounts->first()->data['store_id'],
                $order->reference,
                $vendor->name,
                $vendor->contact_phone,
                $order->customer->first_name,
                $order->billingAddress->contact_phone,
                $order->billingAddress->line_one,
                $recipientCityId,
                $recipientZoneId,
                $recipientAreaId,
                Pathao::DELIVERY_TYPE_NORMAL,
                Pathao::ITEM_TYPE_PARCEL,
                $order->billingAddress->delivery_instructions,
                1,
                0.5,
                $amountToCollect->decimal(),
                ''
            )),
        ])->andReturn(new CreateNewOrderResponseData(
            '',
            'success',
            200,
            new NewOrderData(
                $consignmentId = $this->faker->randomDigit(),
                $order->getKey(),
                $orderStatus = 'Pending',
                $deliveryFee = 80
            )
        ));

        $response = $this->postJson(route('orders.pickup-pathao', $order->getKey()), [
            'recipient_city' => [
                'id' => $recipientCityId,
                'name' => $recipientCityName = $this->faker->city(),
            ],
            'recipient_zone' => [
                'id' => $recipientZoneId,
                'name' => $recipientZoneName = $this->faker->name(),
            ],
            'recipient_area' => [
                'id' => $recipientAreaId,
                'name' => $recipientAreaName = $this->faker->name(),
            ],
        ]);

        $response->assertCreated()
            ->assertJson([
                'id' => $order->getKey(),
                'meta' => [
                    'shipping_info' => [
                        'consignment_id' => $consignmentId,
                        'merchant_order_id' => (string) $order->getKey(),
                        'order_status' => $orderStatus,
                        'delivery_fee' => $deliveryFee,
                    ],
                ],
            ]);
    }

    public function test_seller_can_not_make_pickup_request_for_not_placed_order(): void
    {
        /** @var User */
        $seller = User::factory()->create();
        Sanctum::actingAs($seller);

        $order = $this->createOrder($seller);
        $recipientCityId = 1;
        $recipientZoneId = 1;
        $recipientAreaId = 1;

        Saloon::fake([
            GetAccessTokenRequest::class => MockResponse::make([
                'access_token' => $this->faker->sha1(),
                'refresh_token' => $this->faker->sha1(),
                'token_type' => 'Bearer',
                'expires_in' => now()->addHours(1)->timestamp,
            ]),
        ]);
        Pathao::shouldReceive('createNewOrder')->never();

        $response = $this->postJson(route('orders.pickup-pathao', $order->getKey()), [
            'recipient_city' => [
                'id' => $recipientCityId,
                'name' => $recipientCityName = $this->faker->city(),
            ],
            'recipient_zone' => [
                'id' => $recipientZoneId,
                'name' => $recipientZoneName = $this->faker->name(),
            ],
            'recipient_area' => [
                'id' => $recipientAreaId,
                'name' => $recipientAreaName = $this->faker->name(),
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_seller_can_not_make_pickup_request_for_another_sellers_order(): void
    {
        /** @var User */
        $seller = User::factory()->create();
        /** @var User */
        $seller2 = User::factory()->create();
        Sanctum::actingAs($seller2);

        $order = $this->createOrder($seller);

        $order->update([
            'placed_at' => now(),
        ]);

        $recipientCityId = 1;
        $recipientZoneId = 1;
        $recipientAreaId = 1;

        Saloon::fake([
            GetAccessTokenRequest::class => MockResponse::make([
                'access_token' => $this->faker->sha1(),
                'refresh_token' => $this->faker->sha1(),
                'token_type' => 'Bearer',
                'expires_in' => now()->addHours(1)->timestamp,
            ]),
        ]);
        Pathao::shouldReceive('createNewOrder')->never();

        $response = $this->postJson(route('orders.pickup-pathao', $order->getKey()), [
            'recipient_city' => [
                'id' => $recipientCityId,
                'name' => $recipientCityName = $this->faker->city(),
            ],
            'recipient_zone' => [
                'id' => $recipientZoneId,
                'name' => $recipientZoneName = $this->faker->name(),
            ],
            'recipient_area' => [
                'id' => $recipientAreaId,
                'name' => $recipientAreaName = $this->faker->name(),
            ],
        ]);

        $response->assertNotFound();
    }
}
