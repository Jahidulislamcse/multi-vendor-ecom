<?php

namespace App\Queries;

use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ProductIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = Product::query()
            ->withCount(['vendor', 'prices', 'productType']);

        parent::__construct($query, $request);

        // $this->allowedFilters();
    }
}
