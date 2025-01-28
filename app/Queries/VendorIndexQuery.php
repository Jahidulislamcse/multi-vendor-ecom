<?php

namespace App\Queries;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class VendorIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = Vendor::query()
            ->withCount(['followers', 'products']);

        parent::__construct($query, $request);

        // $this->allowedFilters();
    }
}
