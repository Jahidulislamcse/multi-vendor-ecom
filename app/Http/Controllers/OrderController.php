<?php

namespace App\Http\Controllers;

use App\Data\Resources\OrderData;
use App\Queries\OrderIndexQuery;
use Lunar\Models\Order;
use Spatie\LaravelData\PaginatedDataCollection;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(OrderIndexQuery $ordersQuery): PaginatedDataCollection
    {
        $orders = $ordersQuery->paginate();

        return new PaginatedDataCollection(OrderData::class, $orders);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): OrderData
    {
        return OrderData::from($order);
    }
}
