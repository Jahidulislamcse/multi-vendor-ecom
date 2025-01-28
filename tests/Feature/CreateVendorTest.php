<?php

namespace Tests\Feature;

use App\Http\Integrations\Pathao\Requests\CreateNewStoreRequest;
use App\Http\Integrations\Pathao\Requests\GetAccessTokenRequest;
use App\Jobs\SubmitVendorDataToPathaoToCreateStore;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Queue;
use Saloon;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

class CreateVendorTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_to_create_vendor_needs_to_be_authenticated(): void
    {
        $response = $this->postJson(route('vendors.store'), []);

        $response
            ->assertUnauthorized();
    }

    public function test_to_create_vendor_needs_to_provide_name_desc_addr(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('vendors.store'), []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                ['name', 'description', 'address', 'contact_email', 'contact_phone', 'city', 'area', 'zone']
            );
    }

    public function test_after_creating_the_vendor_it_returns_the_vendor_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $name = $this->faker->company();
        $address = $this->faker->address();
        $description = $this->faker->paragraph();
        $contactEmail = $this->faker->email();
        $contactPhone = $this->faker->phoneNumber();

        Queue::fake();

        $response = $this->postJson(route('vendors.store'), [
            'name' => $name,
            'address' => $address,
            'description' => $description,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'city' => [
                'id' => 1,
                'name' => $this->faker->city(),
            ],
            'area' => [
                'id' => 1,
                'name' => $this->faker->city(),
            ],
            'zone' => [
                'id' => 1,
                'name' => $this->faker->city(),
            ],
        ]);
        $response
            ->assertCreated();
        $this->assertDatabaseCount(Vendor::getModel()->getTable(), 1);
        $vendor = Vendor::first();
        $response
            ->assertJson([
                'id' => $vendor->getKey(),
                'name' => $vendor->name,
                'address' => $vendor->address,
                'description' => $vendor->description,
                'contact_email' => $vendor->contact_email,
                'contact_phone' => $vendor->contact_phone,
            ]);
        Queue::assertPushed(SubmitVendorDataToPathaoToCreateStore::class);
    }

    public function test_vendor_data_submit_to_provider(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $name = $this->faker->company();
        $address = $this->faker->address();
        $description = $this->faker->paragraph();
        $contactEmail = $this->faker->email();
        $contactPhone = $this->faker->phoneNumber();

        Saloon::fake([
            GetAccessTokenRequest::class => MockResponse::make(body: [
                'access_token' => $this->faker->sha1(),
                'refresh_token' => $this->faker->sha1(),
                'token_type' => 'Bearer',
                'expires_in' => now()->addHours(1)->timestamp,
            ]),
            CreateNewStoreRequest::class => MockResponse::make(body: [
                'message' => 'Store created successfully, Please wait one hour for approval.',
                'type' => 'success',
                'code' => 200,
                'data' => [
                    'store_id' => $storeId = 13804,
                    'store_name' => $storeName = $this->faker->company(),
                ],
            ]),
        ]);

        $response = $this->postJson(route('vendors.store'), [
            'name' => $name,
            'address' => $address,
            'description' => $description,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'city' => [
                'id' => $cityId = 1,
                'name' => $cityName = $this->faker->city(),
            ],
            'area' => [
                'id' => $areaId = 1,
                'name' => $areaName = $this->faker->city(),
            ],
            'zone' => [
                'id' => $zoneId = 1,
                'name' => $zoneName = $this->faker->city(),
            ],
        ]);
        $response
            ->assertCreated();
        $this->assertDatabaseCount(Vendor::getModel()->getTable(), 1);
        $vendor = Vendor::first();
        $response
            ->assertJson([
                'id' => $vendor->getKey(),
                'name' => $vendor->name,
                'address' => $vendor->address,
                'description' => $vendor->description,
                'contact_email' => $vendor->contact_email,
                'contact_phone' => $vendor->contact_phone,
            ]);

        $this->assertEquals($vendor->deliveryProviderAccounts->first()->data['city'], [
            'id' => $cityId,
            'name' => $cityName,
        ]);
        $this->assertEquals($vendor->deliveryProviderAccounts->first()->data['area'], [
            'id' => $areaId,
            'name' => $areaName,
        ]);
        $this->assertEquals($vendor->deliveryProviderAccounts->first()->data['zone'], [
            'id' => $zoneId,
            'name' => $zoneName,
        ]);
        $this->assertEquals($vendor->deliveryProviderAccounts->first()->data['store'], [
            'id' => $storeId,
            'name' => $storeName,
        ]);
    }

    public function test_user_can_create_only_one_vendor(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $name = $this->faker->company();
        $address = $this->faker->address();
        $description = $this->faker->paragraph();
        $contactEmail = $this->faker->email();
        $contactPhone = $this->faker->phoneNumber();

        $user->vendors()->create(compact('name', 'address', 'description') + [
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
        ]);

        $response = $this->postJson(route('vendors.store'), compact('name', 'description', 'address') + [
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
        ]);
        $response
            ->assertForbidden();
        $this->assertDatabaseCount(Vendor::getModel()->getTable(), 1);
    }
}
