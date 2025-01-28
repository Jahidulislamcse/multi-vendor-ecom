<?php

namespace App\Http\Integrations\Pathao\Requests;

use App\Data\Dto\Pathao\CreateNewOrderRequestData;
use App\Data\Dto\Pathao\CreateNewOrderResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class CreateNewOrderRequest extends Request implements HasBody
{
    use AcceptsJson, HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/aladdin/api/v1/orders';
    }

    public function __construct(public readonly CreateNewOrderRequestData $data)
    {

    }

    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return CreateNewOrderResponseData::from($response->json());
    }
}
