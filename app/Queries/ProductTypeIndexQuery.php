<?php

namespace App\Queries;

use Illuminate\Http\Request;
use Lunar\Models\ProductType;
use Spatie\QueryBuilder\QueryBuilder;

class ProductTypeIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = ProductType::query()
            ->withCount(['mappedAttributes']);

        parent::__construct($query, $request);

        // $this->allowedFilters();
    }
}
