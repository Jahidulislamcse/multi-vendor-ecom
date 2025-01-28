<?php

namespace App\Http\Integrations\SSLCommerz\Requests;

use App\Data\Dto\SSLCommerz\OrderValidationRequestData;
use App\Data\Dto\SSLCommerz\OrderValidationResponseData;
use Saloon\Contracts\Authenticator;
use Saloon\Enums\Method;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Auth\QueryAuthenticator;
use Saloon\Http\Request;
use Saloon\Http\Response;

class OrderValidationRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(protected readonly OrderValidationRequestData $data)
    {

    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/validator/api/validationserverAPI.php';
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new MultiAuthenticator(
            new QueryAuthenticator('format', 'json'),
            new QueryAuthenticator('val_id', $this->data->valId),
            new QueryAuthenticator('store_id', config('services.sslcommerz.store_id')),
            new QueryAuthenticator('store_passwd', config('services.sslcommerz.store_password')),
        );
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json();

        return OrderValidationResponseData::from($data);
    }
}
