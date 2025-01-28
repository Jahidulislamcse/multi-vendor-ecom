<?php

namespace Database\Seeders;

use App\Models\Product;
use DB;
use Illuminate\Database\Seeder;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            if (! Country::count()) {
                $this->command->info('Importing countries');
                $this->command->call('app:import-address-data');
            }

            if (! Channel::whereDefault(true)->exists()) {
                $this->command->info('Setting up default channel');

                Channel::create([
                    'name' => 'Webstore',
                    'handle' => 'webstore',
                    'default' => true,
                    'url' => 'https://fleepness.com',
                ]);
            }

            if (! Language::count()) {
                $this->command->info('Adding default language');

                Language::create([
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true,
                ]);
            }

            if (! Currency::whereDefault(true)->exists()) {
                $this->command->info('Adding a default currency (BDT)');

                Currency::create([
                    'code' => 'BDT',
                    'name' => 'BD Taka',
                    'exchange_rate' => 1,
                    'decimal_places' => 2,
                    'default' => true,
                    'enabled' => true,
                ]);
            }

            if (! CustomerGroup::whereDefault(true)->exists()) {
                $this->command->info('Adding a default customer group.');

                CustomerGroup::create([
                    'name' => 'Retail',
                    'handle' => 'retail',
                    'default' => true,
                ]);
            }

            if (! CollectionGroup::count()) {
                $this->command->info('Adding an initial collection group');

                CollectionGroup::create([
                    'name' => 'Main',
                    'handle' => 'main',
                ]);
            }

            if (! TaxClass::count()) {
                $this->command->info('Adding a default tax class.');

                TaxClass::create([
                    'name' => 'Default Tax Class',
                    'default' => true,
                ]);
            }

            if (! Attribute::count()) {
                $this->command->info('Setting up initial attributes');

                $group = AttributeGroup::create([
                    'attributable_type' => Product::class,
                    'name' => collect([
                        'en' => 'Details',
                    ]),
                    'handle' => 'details',
                    'position' => 1,
                ]);

                $collectionGroup = AttributeGroup::create([
                    'attributable_type' => Collection::class,
                    'name' => collect([
                        'en' => 'Details',
                    ]),
                    'handle' => 'collection_details',
                    'position' => 1,
                ]);

                Attribute::create([
                    'attribute_type' => Product::class,
                    'attribute_group_id' => $group->id,
                    'position' => 1,
                    'name' => [
                        'en' => 'Name',
                    ],
                    'handle' => 'name',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type' => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position' => 1,
                    'name' => [
                        'en' => 'Name',
                    ],
                    'handle' => 'name',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type' => Product::class,
                    'attribute_group_id' => $group->id,
                    'position' => 2,
                    'name' => [
                        'en' => 'Description',
                    ],
                    'handle' => 'description',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);

                Attribute::create([
                    'attribute_type' => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position' => 2,
                    'name' => [
                        'en' => 'Description',
                    ],
                    'handle' => 'description',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);
            }

            if (! ProductType::count()) {
                $this->command->info('Adding a product type.');

                $type = ProductType::create([
                    'name' => 'Stock',
                ]);

                $type->mappedAttributes()->attach(
                    Attribute::whereAttributeType(Product::class)->get()->pluck('id')
                );

                $productTypes = [
                    'Bag',
                    'Cosmetics',
                    'Clothings',
                    'Books',
                    'Arts & Crafts',
                    'Photo Frame',
                    'Grocery',
                    'Foods',
                    'Shoe',
                ];

                foreach ($productTypes as $eachType) {
                    $type = ProductType::create(
                        ['name' => $eachType]
                    );

                    $type
                        ->mappedAttributes()
                        ->attach(
                            Attribute::whereAttributeType(Product::class)->get()->pluck('id')
                        );
                }
            }
        });

    }
}
