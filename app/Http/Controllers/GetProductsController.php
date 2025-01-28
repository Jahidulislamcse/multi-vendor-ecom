<?php

namespace App\Http\Controllers;

use App\Data\Resources\ProductData;
use App\Queries\ProductIndexQuery;
use Spatie\LaravelData\PaginatedDataCollection;

class GetProductsController extends Controller
{
    public function __invoke(ProductIndexQuery $productQuery): PaginatedDataCollection
    {
        $products = $productQuery->paginate();

        return (new PaginatedDataCollection(ProductData::class, $products))
            ->include(
                'vendor',
                'prices',
                'variants',
                'productType',
            );
    }
}
