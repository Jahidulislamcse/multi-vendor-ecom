<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Tests\TestCase;

class UpdateVendorProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_authenticated_to_update_product_to_vendor(): void
    {
        Language::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create();
        $response = $this->putJson(route('vendor-products.update', [$vendor->getKey(), $product->getKey()]), []);

        $response->assertUnauthorized();
    }

    public function test_user_needs_to_provide_attributes_for_product_type_if_it_is_changed(): void
    {
        Language::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $product = Product::factory()->for($vendor)->create();

        $newProductType = ProductType::factory()->hasMappedAttributes(
            Attribute::factory()->count(3),
            [
                'required' => true,
                'system' => true,
                'name' => ['en' => 'test'],
            ]
        )->create();

        $response = $this->putJson(route('vendor-products.update', [$vendor->getKey(), $product->getKey()]), [
            'product_type_id' => $newProductType->getKey(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attribute_data.test',
            ]);
    }

    public function test_user_needs_to_provide_attributes_data_if_it_is_not_null_or_present_in_the_payload(): void
    {
        Language::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $product = Product::factory()->for($vendor)->create();
        $attrGrp = AttributeGroup::factory()->create();

        Attribute::factory()->for($attrGrp)->create([
            'required' => true,
            'system' => true,
            'name' => ['en' => 'name'],
            'attribute_type' => Product::class,
        ]);

        Attribute::factory()->for($attrGrp)->create([
            'required' => true,
            'system' => true,
            'name' => ['en' => 'description'],
            'attribute_type' => Product::class,
        ]);

        $response = $this->putJson(route('vendor-products.update', [$vendor->getKey(), $product->getKey()]), [
            'attribute_data' => [
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attribute_data.name',
                'attribute_data.description',
            ]);
    }

    public function test_user_needs_to_be_owner_of_vendor_to_update_product_to_vendor(): void
    {
        Language::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create();

        $response = $this->putJson(
            route('vendor-products.update',
                [$vendor->getKey(), $product->getKey(),
                ]),
            [

            ]);

        $response->assertNotFound();

    }

    public function test_after_successful_update_of_product_response_will_be_updated_product(): void
    {
        $taxClass = TaxClass::factory()->create([
            'default' => true,
        ]);
        Currency::factory()->create();
        Language::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $vendor = Vendor::factory()->for($user)->create();
        $product = Product::factory()->for($vendor)->create();
        $baseVariant = $product->variants()->create([
            'sku' => $this->faker->randomAscii(),
            'tax_class_id' => $taxClass->getKey(),
        ]);
        $baseVariant->basePrices()->create([
            'price' => $this->faker->randomFloat(),
        ]);

        $attrGrp = AttributeGroup::factory()->create();
        Attribute::factory()->for($attrGrp)->create([
            'required' => true,
            'system' => true,
            'name' => ['en' => 'name'],
            'attribute_type' => Product::class,
        ]);
        Attribute::factory()->for($attrGrp)->create([
            'required' => true,
            'system' => true,
            'name' => ['en' => 'description'],
            'attribute_type' => Product::class,
        ]);

        $response = $this->putJson(route('vendor-products.update', [$vendor->getKey(), $product->getKey()]), [
            'attribute_data' => [
                'name' => $name = $this->faker->name(),
                'description' => $desc = $this->faker->sentence(),
            ],
            'sku' => $sku = $this->faker->randomAscii(),
            'price' => $price = $this->faker->randomFloat(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'attribute_data' => [
                    'name',
                    'description',
                ],
                'variants' => [
                    '*' => [
                        'sku',
                    ],
                ],
                'prices' => [
                    '*' => [
                        'price',
                    ],
                ],
            ]);

        $this->assertDatabaseHas(Product::getModel()->getTable(), [
            'attribute_data->name->value' => $name,
            'attribute_data->description->value' => $desc,
        ]);
        $this->assertDatabaseCount(Product::getModel()->getTable(), 1);
    }
}
