<?php

namespace App\Http\Controllers;

use App\Constants\SupportedPaymentMethods;
use App\Data\Dto\SSLCommerz\InitiatePaymentResponseData;
use Lunar\Base\PaymentTypeInterface;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;

class InitiatePaymentController extends Controller
{
    public function sslcommerz(Cart $cart): InitiatePaymentResponseData
    {
        /** @var PaymentTypeInterface */
        $driver = Payments::driver(SupportedPaymentMethods::SSLCOMMERZ->value);

        $driver->cart($cart)
            ->authorize();

        $cart->refresh();
        $data = InitiatePaymentResponseData::from($cart->meta); /** @phpstan-ignore-line */

        return $data;
    }

    public function cod(Cart $cart)
    {
        $driver = Payments::driver(SupportedPaymentMethods::COD->value);

        $driver->cart($cart);

        $driver->authorize();
    }

    public function __invoke(Cart $cart, SupportedPaymentMethods $supportedPaymentMethod): ?InitiatePaymentResponseData
    {
        return match ($supportedPaymentMethod) {
            SupportedPaymentMethods::COD => $this->cod($cart),
            SupportedPaymentMethods::SSLCOMMERZ => $this->sslcommerz($cart),
        };
    }
}
