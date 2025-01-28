<?php

namespace Tests\Feature;

use App\Data\Resources\CustomerData;
use Database\Seeders\ApplicationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Customer;
use Tests\TestCase;

class CustomerDataTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationSeeder::class);
    }

    public function test_customer_data(): void
    {
        $customer = Customer::factory()->create();

        $customer->meta = [
            'text' => $this->faker->paragraph(),
        ];

        $customer->attribute_data = [
            'something' => new TranslatedText(collect([
                'en' => new Text($this->faker->title()),
            ])),
        ];

        $customer->save();

        $this->assertIsArray(CustomerData::from($customer)->meta);
        $this->assertIsArray(CustomerData::from($customer)->attributeData);
    }
}
