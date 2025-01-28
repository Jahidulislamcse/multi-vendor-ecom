<?php

namespace App\Http\Controllers;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use App\Constants\ProductStatuses;
use App\Data\Dto\CreateVendorProductData;
use App\Data\Dto\UpdateVendorProductData;
use App\Data\Resources\ProductData;
use App\Models\Product;
use App\Models\Vendor;
use DB;
use Lunar\FieldTypes\Text;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VendorProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('show');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateVendorProductData $data, Vendor $vendor): ProductData
    {

        $product = DB::transaction(function () use ($data, $vendor) {
            $product = $vendor
                ->products()
                ->create([
                    'status' => ProductStatuses::PUBLISHED,
                    'product_type_id' => $data->productTypeId,
                    'attribute_data' => collect($data->attributeData)->map(fn (string $attValue) => new Text($attValue)),
                ]);

            $product->addAllMediaFromTokens($data->thumbnailPicture, 'images');
            $product->getFirstMedia('images')->setCustomProperty('primary', true)->save();
            $product->addAllMediaFromTokens($data->pictures, 'images');

            $taxClass = TaxClass::getDefault();

            /** @var ProductVariant */
            $baseVariant = $product->variants()->create([
                'tax_class_id' => $taxClass->getKey(),
                'sku' => $data->sku,
            ]);
            $baseVariant->basePrices()->create([
                'price' => $data->price,
            ]);

            return $product;
        }, 2);

        $product->load('vendor');
        $product->load('prices');
        $product->refresh();

        return ProductData::from($product)->include(
            'vendor',
            'prices',
            'variants',
            'productType',
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): ProductData
    {
        $product->load('vendor');
        $product->load('prices');

        return ProductData::from($product)->include(
            'vendor',
            'prices',
            'variants',
            'productType',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendorProductData $data, Vendor $vendor, Product $product): ProductData
    {
        return DB::transaction(function () use ($data, $product) {
            /** @var ProductVariant */
            $baseVariant = $product->variants->first();
            /** @var Price */
            $baseVariantPrice = $baseVariant->basePrices()->first();

            if (is_array($data->attributeData)) {
                $product->attribute_data = collect($data->attributeData)->map(fn (string $attValue) => new Text($attValue));
            }

            if (is_int($data->productTypeId)) {
                $product->product_type_id = $data->productTypeId;
            }

            if (is_string($data->thumbnailPicture)) {
                /** @var Media */
                $previousThumbnail = $product->getFirstMedia('images', ['primary' => true]);
                $previousThumbnail->delete();

                /** @var TemporaryFile */
                $temporaryFile = TemporaryFile::whereToken($data->thumbnailPicture)->first();
                $temporaryFile->getFirstMedia('images')->setCustomProperty('primary', true)->save();
                $product->addAllMediaFromTokens($data->thumbnailPicture, 'images');
            }

            if (is_string($data->pictures)) {
                $product->addAllMediaFromTokens($data->pictures, 'images');
            }

            if (is_string($data->sku)) {
                $baseVariant->sku = $data->sku;
            }

            if (is_numeric($data->price)) {
                $baseVariantPrice->price = $data->price;
            }

            if ($product->isDirty()) {
                $product->save();
            }

            if ($baseVariant->isDirty()) {
                $baseVariant->save();
            }

            if ($baseVariantPrice->isDirty()) {
                $baseVariantPrice->save();
            }

            $product->load('vendor');
            $product->load('prices');
            $product->refresh();

            return ProductData::from($product)->include(
                'vendor',
                'prices',
                'variants',
                'productType',
            );
        });
    }
}
