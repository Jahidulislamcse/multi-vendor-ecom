<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Vendor;
use Lunar\Database\Factories\ProductFactory as FactoriesProductFactory;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\ProductType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends FactoriesProductFactory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_type_id' => ProductType::factory(),
            'status' => 'published',
            'brand_id' => Brand::factory(),
            'attribute_data' => collect([
                'name' => new Text($this->faker->name),
                'description' => new Text($this->faker->sentence),
            ]),
            'vendor_id' => Vendor::factory(),
        ];
    }
}
