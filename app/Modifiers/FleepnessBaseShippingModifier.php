<?php

namespace App\Modifiers;

use App\Constants\SupportedShippingMethods;
use Lunar\Base\ShippingModifier;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\TaxClass;

class FleepnessBaseShippingModifier extends ShippingModifier
{
    public function handle(Cart $cart)
    {
        // Get the tax class
        $taxClass = TaxClass::getDefault();

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Pathao',
                taxClass: $taxClass,
                description: 'Delivery with pathao',
                price: new Price(0, $cart->currency, 1),
                identifier: SupportedShippingMethods::PATHAO->value,
            )
        );
    }
}
