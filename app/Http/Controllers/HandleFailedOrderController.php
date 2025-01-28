<?php

namespace App\Http\Controllers;

use App\Constants\SupportedPaymentMethods;
use App\Data\Resources\OrderData;
use App\Models\Product;
use DB;
use Lunar\Facades\Payments;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Symfony\Component\HttpFoundation\Response;

class HandleFailedOrderController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Order $order): OrderData
    {
        /** @var OrderLine */
        $firstProductLine = $order->productLines->first();
        /** @var ProductVariant */
        $productVariant = $firstProductLine->purchasable;
        /** @var Product */
        $product = $productVariant->product;
        abort_unless(
            strtolower($order->status) === 'cancelled' ||
            strtolower($order->status) === 'failed' ||
            strtolower($order->status) === 'unattempted' ||
            strtolower($order->status) === 'expired' ||
            $product->vendor->is(auth()->user()->vendors->first()),
            Response::HTTP_FORBIDDEN
        );

        return DB::transaction(function () use ($order) {
            $order->captures->each->delete();
            $order->refunds->each->delete();
            $order->intents->each->delete();

            $driver = Payments::driver(SupportedPaymentMethods::COD->value);

            $driver->cart($order->cart)->withData([
                'authorized' => 'payment-offline',
            ]);

            $driver->authorize();

            $order->refresh();

            return OrderData::from($order);
        });
    }
}
