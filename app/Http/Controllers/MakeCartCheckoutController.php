<?php

namespace App\Http\Controllers;

use App\Constants\SupportedShippingMethods;
use App\Data\Dto\MakeCartCheckoutData;
use App\Data\Resources\OrderData;
use DB;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\Customer;

class MakeCartCheckoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Cart $cart, MakeCartCheckoutData $data): OrderData
    {
        return DB::transaction(function () use ($data, $cart) {
            $countryBdId = Country::whereIso2('BD')->first()->getKey();
            $addrCommonOptions = ['country_id' => $countryBdId, 'shipping_option' => SupportedShippingMethods::PATHAO->value];

            $cart->setShippingAddress($data->shippingAddress->toArray() + $addrCommonOptions);
            $cart->setBillingAddress($data->billingAddress->toArray() + $addrCommonOptions);
            /** @var Customer */
            $customer = Customer::create($data->customer->toArray());
            $cart->update([
                'customer_id' => $customer->getKey(),
            ]);

            $cart->canCreateOrder();

            $order = $cart->createOrder();

            $order->load(['lines' => function ($query) {
                $query->where('type', '!=', 'shipping')->with('purchasable');
            }]);

            return OrderData::from($order);
        });
    }
}
