<?php

namespace App\Http\Controllers;

use App\Constants\GateNames;
use App\Constants\SupportedShippingMethods;
use App\Data\Dto\MakeOrderPickupData;
use App\Data\Dto\Pathao\CreateNewOrderRequestData;
use App\Data\Dto\Pathao\CreateNewOrderResponseData;
use App\Data\Resources\OrderData;
use App\Facades\Pathao;
use App\Models\User;
use App\Models\VendorDeliveryProviderAccount;
use Arr;
use ArrayObject;
use Lunar\DataTypes\Price;
use Lunar\Models\Order;

class MakeOrderPickupRequestToPathaoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Order $order, MakeOrderPickupData $data): OrderData
    {
        $this->authorize(GateNames::MAKE_ORDER_PICKUP_REQUEST->value, $order);

        /** @var User */
        $seller = auth()->user();

        $vendor = $seller->vendors->first();

        /** @var VendorDeliveryProviderAccount */
        $pathaoProvider = $vendor->deliveryProviderAccounts()->where('provider_name', SupportedShippingMethods::PATHAO->value)->first();

        if (! is_null($order->meta) && is_null(Arr::get($order->meta, 'shipping_info.consignment_id'))) {
            /** @var Price */
            $amountToCollect = $order->total;
            /** @var CreateNewOrderResponseData */
            $result = Pathao::createNewOrder(new CreateNewOrderRequestData(
                $pathaoProvider->data['store_id'],
                $order->reference,
                $vendor->name,
                $vendor->contact_phone,
                $order->customer->first_name,
                $order->billingAddress->contact_phone,
                $order->billingAddress->line_one,
                $data->recipientCity['id'],
                $data->recipientZone['id'],
                Arr::get($data->recipientArea, 'id'),
                Pathao::DELIVERY_TYPE_NORMAL, // normal delivery
                Pathao::ITEM_TYPE_PARCEL, // parcel
                $order->billingAddress->delivery_instructions,
                1,
                0.5,
                $amountToCollect->decimal(),
                ''
            ));

            /** @var ArrayObject */
            $meta = $order->meta;
            $order->update([
                'meta' => array_merge($meta->getArrayCopy(), [
                    'shipping_info' => $result->data->toArray(),
                ]),
            ]);
        }

        return OrderData::from($order);
    }
}
