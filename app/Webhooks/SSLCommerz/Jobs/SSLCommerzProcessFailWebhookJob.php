<?php

namespace App\Webhooks\SSLCommerz\Jobs;

use App\Constants\SupportedPaymentMethods;
use App\Data\Dto\SSLCommerz\InstantPaymentNotificationData;
use Lunar\Base\PaymentTypeInterface;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Transaction;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class SSLCommerzProcessFailWebhookJob extends ProcessWebhookJob
{
    public function handle()
    {
        $webhookPayload = $this->webhookCall->payload;

        /** @var PaymentTypeInterface */
        $driver = Payments::driver(SupportedPaymentMethods::SSLCOMMERZ->value);
        $data = InstantPaymentNotificationData::from($webhookPayload);
        /** @var Transaction */
        $transaction = Transaction::whereReference($data->tranId)->first();
        /** @var Cart */
        $cart = $transaction->order->cart;

        $driver
            ->cart($cart)
            ->withData([
                'ipn_data' => $data->toArray(),
            ])
            ->capture($transaction, $data->amount ?? 0);
    }
}
