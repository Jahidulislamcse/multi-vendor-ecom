<?php

namespace App\Http\Integrations\Pathao\Requests;

use App\Data\Dto\Pathao\GetAccessTokenResponseData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class GetAccessTokenRequest extends Request implements HasBody
{
    use AcceptsJson, HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    public function __construct(protected OAuthConfig $oauthConfig)
    {

    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return $this->oauthConfig->getTokenEndpoint();
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultBody(): array
    {
        return [
            'grant_type' => 'password',
            'client_id' => $this->oauthConfig->getClientId(),
            'client_secret' => $this->oauthConfig->getClientSecret(),

            'username' => config('services.pathao.client_email'),
            'password' => config('services.pathao.client_password'),
        ];
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return GetAccessTokenResponseData::from($response->json());
    }
}
