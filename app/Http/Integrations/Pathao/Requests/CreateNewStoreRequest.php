<?php

namespace App\Http\Integrations\Pathao\Requests;

use App\Data\Dto\Pathao\CreateNewStoreRequestData;
use App\Data\Dto\Pathao\CreateNewStoreResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class CreateNewStoreRequest extends Request implements HasBody
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
        return '/aladdin/api/v1/stores';
    }

    public function __construct(private CreateNewStoreRequestData $data)
    {

    }

    protected function defaultBody(): array
    {
        return $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return CreateNewStoreResponseData::from($response->json());
    }
}
