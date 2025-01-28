<?php

namespace App\Http\Controllers;

use App\Data\Resources\ProductTypeData;
use App\Queries\ProductTypeIndexQuery;
use Lunar\Models\ProductType;
use Spatie\LaravelData\PaginatedDataCollection;

class ProductTypeController extends Controller
{
    public function index(ProductTypeIndexQuery $productTypesQuery): PaginatedDataCollection
    {
        $productTypes = $productTypesQuery->paginate();

        return (new PaginatedDataCollection(ProductTypeData::class, $productTypes))->include('attributeData');
    }

    public function show(ProductType $productType): ProductTypeData
    {
        return ProductTypeData::from($productType)->include('attributeData');
    }
}
