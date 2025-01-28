<?php

namespace App\Http\Integrations\SSLCommerz\Requests;

use App\Data\Dto\SSLCommerz\InitiateRefundRequestData;
use App\Data\Dto\SSLCommerz\InitiateRefundResponseData;
use Saloon\Contracts\Authenticator;
use Saloon\Enums\Method;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Auth\QueryAuthenticator;
use Saloon\Http\Request;
use Saloon\Http\Response;

class InitiateRefundRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(protected readonly InitiateRefundRequestData $data)
    {

    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/validator/api/merchantTransIDvalidationAPI.php';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json();

        return InitiateRefundResponseData::from($data);
    }

    protected function defaultAuth(): ?Authenticator
    {
        /** @var Authenticator[] */
        $authenticators = [
            new QueryAuthenticator('format', 'json'),
            new QueryAuthenticator('bank_tran_id', $this->data->bankTranId),
            new QueryAuthenticator('refund_amount', (string) $this->data->refundAmount),
            new QueryAuthenticator('refund_remarks', $this->data->refundRemarks),
            new QueryAuthenticator('store_id', config('services.sslcommerz.store_id')),
            new QueryAuthenticator('store_passwd', config('services.sslcommerz.store_password')),
        ];

        if (! is_null($this->data->refeId)) {
            array_push($authenticators,
                new QueryAuthenticator('refe_id', $this->data->refeId),
            );
        }

        return new MultiAuthenticator(
            ...$authenticators
        );
    }
}
