<?php

namespace App\Http\Integrations\SSLCommerz\Requests;

use App\Data\Dto\SSLCommerz\TransactionQueryRequestData;
use App\Data\Dto\SSLCommerz\TransactionQueryResponseData;
use Saloon\Contracts\Authenticator;
use Saloon\Enums\Method;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Auth\QueryAuthenticator;
use Saloon\Http\Request;
use Saloon\Http\Response;

class TransactionQueryRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(protected readonly TransactionQueryRequestData $data)
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

        return TransactionQueryResponseData::from($data);
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new MultiAuthenticator(
            new QueryAuthenticator('sessionkey', $this->data->sessionKey),
            new QueryAuthenticator('store_id', config('services.sslcommerz.store_id')),
            new QueryAuthenticator('store_passwd', config('services.sslcommerz.store_password')),
        );
    }
}
