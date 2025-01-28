<?php

namespace App\Actions;

use App\Models\ShippingOption;
use Closure;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

class CreateShippingLine
{
    /**
     * @return Closure
     */
    public function handle(Order $order, Closure $next)
    {
        /** @var Cart $cart */
        $cart = $order->cart->calculate();

        /** @var CartAddress|null $shippingAddress */
        $shippingAddress = $cart->shippingAddress;

        // If we have a shipping address with a shipping option.
        if ($shippingAddress && ($shippingOption = $cart->getShippingOption())
        ) {
            // $shippingLine = $order->lines->first(function ($orderLine) use ($shippingOption) {
            //     return $orderLine->type == 'shipping' &&
            //         $orderLine->purchasable_type == ShippingOption::class &&
            //         $orderLine->identifier == $shippingOption->getIdentifier();
            // }) ?: new OrderLine;

            // $shippingLine->fill([
            //     'order_id' => $order->id,
            //     'purchasable_type' => ShippingOption::class,
            //     'purchasable_id' => 1,
            //     'type' => 'shipping',
            //     'description' => $shippingOption->getDescription(),
            //     'option' => $shippingOption->getOption(),
            //     'identifier' => $shippingOption->getIdentifier(),
            //     'unit_price' => $shippingOption->price->value,
            //     'unit_quantity' => $shippingOption->getUnitQuantity(),
            //     'quantity' => 1,
            //     'sub_total' => $shippingAddress->shippingSubTotal->value,
            //     'discount_total' => $shippingAddress->shippingSubTotal->discountTotal?->value ?: 0,
            //     'tax_breakdown' => $shippingAddress->taxBreakdown,
            //     'tax_total' => $shippingAddress->shippingTaxTotal->value,
            //     'total' => $shippingAddress->shippingTotal->value,
            //     'notes' => null,
            //     'meta' => [

            //     ],
            // ])->save();

            $order->update([
                'meta' => [
                    'shipping_info' => [
                        'description' => $shippingOption->getDescription(),
                        'option' => $shippingOption->getOption(),
                        'identifier' => $shippingOption->getIdentifier(),
                        'unit_price' => $shippingOption->price->value,
                        'unit_quantity' => $shippingOption->getUnitQuantity(),
                        'quantity' => 1,
                        'sub_total' => $shippingAddress->shippingSubTotal->value,
                        'discount_total' => $cart->discountTotal?->value ?: 0,
                        'tax_breakdown' => $shippingAddress->taxBreakdown,
                        'tax_total' => $shippingAddress->shippingTaxTotal->value,
                        'total' => $shippingAddress->shippingTotal->value,
                    ],
                ],
            ]);
        }

        return $next($order->refresh());
    }
}
