<?php

namespace App\Services;

use App\Data\Dto\SSLCommerz\InitiatePaymentRequestData;
use App\Data\Dto\SSLCommerz\InitiatePaymentResponseData;
use App\Data\Dto\SSLCommerz\InitiateRefundRequestData;
use App\Data\Dto\SSLCommerz\InitiateRefundResponseData;
use App\Data\Dto\SSLCommerz\OrderValidationRequestData;
use App\Data\Dto\SSLCommerz\OrderValidationResponseData;
use App\Data\Dto\SSLCommerz\RefundFacadeRequestData;
use App\Data\Dto\SSLCommerz\TransactionQueryRequestData;
use App\Data\Dto\SSLCommerz\TransactionQueryResponseData;
use App\Http\Integrations\SSLCommerz\Requests\InitiatePaymentRequest;
use App\Http\Integrations\SSLCommerz\Requests\InitiateRefundRequest;
use App\Http\Integrations\SSLCommerz\Requests\OrderValidationRequest;
use App\Http\Integrations\SSLCommerz\Requests\TransactionQueryRequest;
use App\Http\Integrations\SSLCommerz\SSLCommerzConnector;

class SslCommerzService
{
    public function __construct(private readonly SSLCommerzConnector $connector)
    {

    }

    public function initiatePayment(InitiatePaymentRequestData $data): InitiatePaymentResponseData
    {
        $response = $this->connector->send(new InitiatePaymentRequest($data));
        /** @var InitiatePaymentResponseData */
        $result = $response->dtoOrFail();

        return $result;
    }

    public function validateOrder(OrderValidationRequestData $data): OrderValidationResponseData
    {
        $response = $this->connector->send(new OrderValidationRequest($data));
        /** @var OrderValidationResponseData */
        $result = $response->dtoOrFail();

        return $result;
    }

    public function refund(RefundFacadeRequestData $data): false|\App\Data\Dto\SSLCommerz\InitiateRefundResponseData
    {
        $sessionKey = $data->sessionKey;

        /** @var TransactionQueryResponseData */
        $transactionQueryResult = $this->connector->send(new TransactionQueryRequest(
            new TransactionQueryRequestData(
                $sessionKey
            )
        ));

        if (! $transactionQueryResult->checkData(
            merchantTransId: $data->merchantTransId,
            merchantTransAmount: $data->merchantTransAmount,
            merchantTransCurrency: $data->merchantTransCurrency
        )) {
            return false;
        }

        /** @var InitiateRefundResponseData */
        $initiateRefundResult = $this->connector->send(new InitiateRefundRequest(
            new InitiateRefundRequestData(
                bankTranId: $transactionQueryResult->bankTranId,
                refundAmount: $data->refundAmount,
                refundRemarks: $data->refundRemarks,
                refeId: $data->refeId
            )
        ));

        return $initiateRefundResult;
    }
}
