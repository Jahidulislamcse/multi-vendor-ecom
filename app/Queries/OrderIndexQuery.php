<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Lunar\Models\Order;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        $query = Order::query();

        parent::__construct($query, $request);

        $this->allowedFilters([
            'status',
            AllowedFilter::callback('vendor_id', function (Builder $query, $vendorId) {
                $query->whereHas('productLines.purchasable.product', function ($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                });
            }),
        ]);
    }
}
