<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Storage;
use Tests\TestCase;

class CreateVendorProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_authenticated_to_add_product_to_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $response = $this->postJson(route('vendor-products.store', $vendor->getKey()), []);

        $response->assertUnauthorized();
    }

    public function test_user_needs_to_provide_valid_input_for_creating_product(): void
    {
        Language::factory()->create();
        $user = User::factory()->create();
        $vendor = Vendor::factory()->for($user)->create();
        Sanctum::actingAs($user);
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

        $response = $this->postJson(route('vendor-products.store', $vendor->getKey()), [
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attribute_data.name',
                'attribute_data.description',
                'sku',
                'price',
                'product_type_id',
            ]);

        $this->assertDatabaseCount(Product::getModel()->getTable(), 0);
    }

    public function test_user_needs_to_provide_attributes_for_product_type(): void
    {

        $productType = ProductType::factory()->hasMappedAttributes(
            Attribute::factory()->count(3),
            [
                'required' => true,
                'system' => true,
                'name' => ['en' => 'something'],
            ]
        )->create();
        Language::factory()->create();
        $user = User::factory()->create();
        $vendor = Vendor::factory()->for($user)->create();
        Sanctum::actingAs($user);
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
        $response = $this->postJson(route('vendor-products.store', $vendor->getKey()), [
            'product_type_id' => $productType->getKey(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'attribute_data.something',
            ]);

        $this->assertDatabaseCount(Product::getModel()->getTable(), 0);
    }

    public function test_user_needs_to_be_owner_of_vendor_to_add_product_to_vendor(): void
    {
        Storage::fake('s3');
        $thumbnailFile = UploadedFile::fake()->image('thumbnail.jpg');
        $productImg = UploadedFile::fake()->image('picture.jpg');

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        Sanctum::actingAs($user);

        $productType = ProductType::factory()->create();
        $name = $this->faker->name();
        $description = $this->faker->paragraph();
        $sku = $this->faker->randomAscii();
        $price = $this->faker->randomFloat();

        $thumbUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $thumbnailFile,
            'collection' => 'images',
        ]);

        $imagesUploadresponse = $this->postJson(route('uploader.media.store'), [
            'files' => [$productImg],
            'collection' => 'images',
        ]);

        $response = $this->postJson(route('vendor-products.store', $vendor->getKey()), [
            'name' => $name,
            'description' => $description,
            'sku' => $sku,
            'price' => $price,
            'product_type_id' => $productType->getKey(),
            'thumbnail_picture' => $thumbUploadresponse->json('token'),
            'pictures' => $imagesUploadresponse->json('token'),
            'attribute_data' => [
                'name' => 'test',
            ],
        ]);
        $response->assertForbidden();
    }

    public function test_after_successful_creation_of_product_response_will_be_created_product(): void
    {
        Storage::fake('s3');
        $thumbnailFile = UploadedFile::fake()->image('thumbnail.jpg');
        $productImg = UploadedFile::fake()->image('picture.jpg');

        Language::factory()->create();
        Currency::factory()->create(['default' => true]);
        TaxClass::factory()->create([
            'default' => true,
        ]);
        $attrGrp = AttributeGroup::factory()->create();
        Attribute::factory()->for($attrGrp)->create([
            'required' => true,
            'system' => true,
            'name' => 'name',
        ]);
        Attribute::factory()->for($attrGrp)->create([
            'required' => true,
            'system' => true,
            'name' => 'description',
        ]);
        $user = User::factory()->create();
        $vendor = Vendor::factory()->for($user)->create();
        Sanctum::actingAs($user);
        $productType = ProductType::factory()->create();
        $name = $this->faker->name();
        $description = $this->faker->paragraph();
        $sku = $this->faker->randomAscii();
        $price = $this->faker->randomFloat();

        $thumbUploadresponse = $this->postJson(route('uploader.media.store'), [
            'file' => $thumbnailFile,
            'collection' => 'images',
        ]);

        $imagesUploadresponse = $this->postJson(route('uploader.media.store'), [
            'files' => [$productImg],
            'collection' => 'images',
        ]);

        $response = $this->postJson(route('vendor-products.store', $vendor->getKey()), [
            'attribute_data' => [
                'name' => $name,
                'description' => $description,
            ],
            'sku' => $sku,
            'price' => $price,
            'product_type_id' => $productType->getKey(),
            'thumbnail_picture' => $thumbUploadresponse->json('token'),
            'pictures' => $imagesUploadresponse->json('token'),
        ]);

        // $response->dd();
        $response
            ->assertCreated()
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
                        'id',
                        'price',
                    ],
                ],
            ]);

        $this->assertDatabaseCount(Product::getModel()->getTable(), 1);
    }
}
