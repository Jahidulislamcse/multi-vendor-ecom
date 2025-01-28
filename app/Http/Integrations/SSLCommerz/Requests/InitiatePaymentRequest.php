<?php

namespace App\Http\Integrations\SSLCommerz\Requests;

use App\Data\Dto\SSLCommerz\InitiatePaymentRequestData;
use App\Data\Dto\SSLCommerz\InitiatePaymentResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasFormBody;

class InitiatePaymentRequest extends Request implements HasBody
{
    use HasFormBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/gwprocess/v4/api.php';
    }

    public function __construct(
        protected readonly InitiatePaymentRequestData $data
    ) {
    }

    protected function defaultBody(): array
    {
        return [
            'store_id' => config('services.sslcommerz.store_id'),
            'store_passwd' => config('services.sslcommerz.store_password'),
            'success_url' => config('services.sslcommerz.success_url'),
            'fail_url' => config('services.sslcommerz.fail_url'),
            'cancel_url' => config('services.sslcommerz.cancel_url'),
            'ipn_url' => config('services.sslcommerz.ipn_url'),
        ] + $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $data = $response->json();

        return InitiatePaymentResponseData::from($data);
    }
}
