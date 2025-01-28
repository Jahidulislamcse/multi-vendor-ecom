<?php

namespace App\Services;

use App\Constants\SupportedPaymentMethods;
use App\Data\Dto\SSLCommerz\InitiatePaymentRequestData;
use App\Data\Dto\SSLCommerz\InitiatePaymentResponseData;
use App\Data\Dto\SSLCommerz\InstantPaymentNotificationData;
use App\Data\Dto\SSLCommerz\OrderValidationRequestData;
use App\Data\Dto\SSLCommerz\OrderValidationResponseData;
use App\Data\Dto\SSLCommerz\RefundFacadeRequestData;
use App\Facades\SSLCommerz;
use DB;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;

class SslCommerzPaymentType extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): PaymentAuthorize
    {
        return DB::transaction(function () {
            if ($this->cart->hasCompletedOrders()) {
                return new PaymentAuthorize(
                    success: true,
                    orderId: $this->cart->completedOrder->getKey()
                );
            }

            if (! $this->order) {
                /** @var Order|null */
                $firstDraftOrder = $this->cart->draftOrder()->first();
                if (! $this->order = $firstDraftOrder) {
                    $this->order = $this->cart->createOrder();
                }
            }
            if ($this->order->intents->count() === 0) {
                /** @var Price */
                $totalPrice = $this->order->total;
                /** @var Transaction */
                $transaction = $this->order
                    ->intents()
                    ->updateOrCreate(
                        [
                            'driver' => SupportedPaymentMethods::SSLCOMMERZ->value,
                        ],
                        [
                            'notes' => '',
                            'card_type' => '',
                            'type' => 'intent',
                            'success' => false,
                            'status' => 'pending',
                            'amount' => $totalPrice->value,
                            'order_id' => $this->order->getKey(),
                            'reference' => $this->order->reference,
                        ]);

                /** @var Price */
                $amount = $transaction->amount;
                /** @var InitiatePaymentResponseData */
                $data = SSLCommerz::initiatePayment(new InitiatePaymentRequestData(
                    tranId: $transaction->reference,
                    totalAmount: $amount->decimal(),
                    currency: $this->cart->currency->code,

                    cusName: $this->cart->customer->first_name,
                    cusEmail: $this->cart->shippingAddress->contact_email,
                    cusAdd1: $this->cart->shippingAddress->line_one,
                    cusAdd2: $this->cart->shippingAddress->line_two,
                    cusCity: $this->cart->shippingAddress->city,
                    cusState: $this->cart->shippingAddress->state,
                    cusPostcode: $this->cart->shippingAddress->postcode,
                    cusCountry: $this->cart->shippingAddress->country->iso2,
                    cusPhone: $this->cart->shippingAddress->contact_phone,
                    cusFax: '',

                    shipName: $this->cart->shippingAddress->title,
                    shipAdd1: $this->cart->shippingAddress->line_one,
                    shipAdd2: $this->cart->shippingAddress->line_two,
                    shipCity: $this->cart->shippingAddress->city,
                    shipState: $this->cart->shippingAddress->state,
                    shipPostcode: $this->cart->shippingAddress->postcode,
                    shipPhone: $this->cart->shippingAddress->contact_phone,
                    shipCountry: $this->cart->shippingAddress->country->iso2,
                    shippingMethod: 'NO',

                    productName: $this->cart
                        ->lines
                        ->map(fn (CartLine $line) => $line->purchasable->getDescription())
                        ->join(', '),
                    productCategory: $this->cart
                        ->lines
                        ->map(fn (CartLine $line) => $line->purchasable->product->productType->name)
                        ->unique()
                        ->join(', '),
                    productProfile: 'general',

                    valueA: '',
                    valueB: '',
                    valueC: '',
                    valueD: '',
                ));

                $cartMeta = $data->toArray();

                $this->cart->update([
                    'meta' => $cartMeta,
                ]);
            }

            return new PaymentAuthorize(true, null, $this->order->getKey());
        });
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        if (! $transaction->order->isPlaced()) {
            return new PaymentRefund(false);
        }

        /** @var Price */
        $merchantTransAmount = $transaction->amount;

        /** @var false|\App\Data\Dto\SSLCommerz\InitiateRefundResponseData */
        $result = SSLCommerz::refund(new RefundFacadeRequestData(
            sessionKey: $this->cart->meta['sessionkey'], //@phpstan-ignore-line
            merchantTransId: $transaction->reference,
            merchantTransAmount: $merchantTransAmount->decimal(),
            merchantTransCurrency: $transaction->currency->code,
            refundAmount: $amount,
            refundRemarks: $notes ?? '',
            refeId: $transaction->reference,
        ));

        if ($result === false) {
            return new PaymentRefund(false);
        }

        $success = $result->isValid();

        if ($success) {
            Transaction::create([
                'type' => 'refund',
                'notes' => $notes,
                'amount' => $amount,
                'success' => $success,
                'status' => $result->status,
                'driver' => $transaction->driver,
                'order_id' => $transaction->order_id,
                'reference' => $transaction->reference,
                'last_four' => $transaction->last_four,
                'card_type' => $transaction->card_type,
                'parent_transaction_id' => $transaction->id,
            ]);
        }

        return new PaymentRefund($success);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        if ($transaction->order->isPlaced()) {
            return new PaymentCapture(true);
        }

        return DB::transaction(function () use ($transaction, $amount) {
            $data = InstantPaymentNotificationData::from($this->data['ipn_data']);
            /** @var Cart */
            $cart = $transaction->order->cart;

            $success = $data->isValid();

            if ($success) {
                /** @var OrderValidationResponseData */
                $validationData = SSLCommerz::validateOrder(new OrderValidationRequestData(
                    valId: $data->valId
                ));

                if (! $validationData->checkData(
                    merchantTransId: $data->tranId,
                    merchantTransAmount: $cart->total->decimal(),
                    merchantTransCurrency: $cart->currency->code
                )) {
                    return new PaymentCapture(false);
                }
            }

            $transaction->order->captures()->updateOrCreate([
                'driver' => $transaction->driver,
            ],
                [
                    'type' => 'capture',
                    'amount' => $amount,
                    'success' => $success,
                    'captured_at' => now(),
                    'status' => $data->status,
                    'notes' => $transaction->notes,
                    'order_id' => $transaction->order_id,
                    'reference' => $transaction->reference,
                    'last_four' => $transaction->last_four,
                    'card_type' => $transaction->card_type,
                    'parent_transaction_id' => $transaction->id,
                ]);

            // Transaction::create([

            // ]);

            $transaction
                ->order()
                ->update([
                    'placed_at' => $success ? now() : null,
                    'status' => $success ? 'payment-received' : $data->status,
                ]);

            return new PaymentCapture($success);
        }, 2);
    }
}
