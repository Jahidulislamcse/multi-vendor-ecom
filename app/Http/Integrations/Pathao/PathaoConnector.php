<?php

namespace App\Http\Integrations\Pathao;

use App\Data\Dto\Pathao\GetAccessTokenResponseData;
use App\Http\Integrations\Pathao\Requests\GetAccessTokenRequest;
use App\Http\Integrations\Pathao\Requests\GetRefreshTokenRequest;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class PathaoConnector extends Connector
{
    use AcceptsJson, AlwaysThrowOnErrors, ClientCredentialsGrant;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('services.pathao.base_url');
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId(config('services.pathao.client_id'))
            ->setClientSecret(config('services.pathao.client_secret'))
            ->setTokenEndpoint('/aladdin/api/v1/issue-token');
    }

    /**
     * Refresh the access token.
     *
     * @template TRequest of \Saloon\Http\Request
     *
     * @param  callable(TRequest): (void)|null  $requestModifier
     */
    public function refreshAccessToken(OAuthAuthenticator|string $refreshToken, bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate(withRedirectUrl: false);

        if ($refreshToken instanceof OAuthAuthenticator) {
            if ($refreshToken->isNotRefreshable()) {
                throw new InvalidArgumentException('The provided OAuthAuthenticator does not contain a refresh token.');
            }

            $refreshToken = $refreshToken->getRefreshToken();
        }

        $request = $this->resolveRefreshTokenRequest($this->oauthConfig(), $refreshToken);

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);

        if ($returnResponse === true) {
            return $response;
        }

        $response->throw();

        return $this->createOAuthAuthenticatorFromResponse($response);
    }

    /**
     * Create the OAuthAuthenticator from a response.
     */
    protected function createOAuthAuthenticatorFromResponse(Response $response): OAuthAuthenticator
    {
        /** @var GetAccessTokenResponseData */
        $responseData = $response->dto();

        $accessToken = $responseData->accessToken;
        $refreshToken = $responseData->refreshToken;
        $expiresAt = null;

        if (isset($responseData->expiresIn) && is_numeric($responseData->expiresIn)) {
            $expiresAt = (new DateTimeImmutable())->add(
                DateInterval::createFromDateString((int) $responseData->expiresIn.' seconds')
            );
        }

        return new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);
    }

    /**
     * Resolve the refresh token request
     */
    protected function resolveRefreshTokenRequest(OAuthConfig $oauthConfig, string $refreshToken): Request
    {
        return new GetRefreshTokenRequest($oauthConfig, $refreshToken);
    }

    /**
     * Resolve the access token request
     */
    protected function resolveAccessTokenRequest(OAuthConfig $oauthConfig, array $scopes = [], string $scopeSeparator = ' '): Request
    {
        return new GetAccessTokenRequest($oauthConfig);
    }

    public function boot(PendingRequest $pendingRequest): void
    {
        if ($pendingRequest->getRequest() instanceof GetAccessTokenRequest) {
            return;
        }

        $cachekey = $this::class.'authenticator';

        $serialized = cache($cachekey);

        if (! is_null($serialized)) {
            $authenticator = AccessTokenAuthenticator::unserialize($serialized);
        } elseif (is_null($serialized)) {
            /** @var AccessTokenAuthenticator */
            $authenticator = $this->getAccessToken();
            cache()->forever($cachekey, $authenticator->serialize());
        }

        if ($authenticator->hasExpired()) {
            /** @var AccessTokenAuthenticator */
            $authenticator = $this->refreshAccessToken($authenticator);
            cache()->forever($cachekey, $authenticator->serialize());
        }

        $pendingRequest->authenticate($authenticator);
    }
}
