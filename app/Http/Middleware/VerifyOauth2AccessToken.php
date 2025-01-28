<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidAccessTokenException;
use App\Exceptions\InvalidInputException;
use Closure;
use Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOauth2AccessToken
{
    protected function getIntrospect(string $accessToken)
    {
        $response = Http::asForm()
            ->withBasicAuth(config('authorizationserver.authorization_server_client_id'), config('authorizationserver.authorization_server_client_secret'))
            ->post(
                config('authorizationserver.authorization_server_introspect_url'),
                [
                    'token_type_hint' => 'requesting_party_token',

                    // This is the access token for verifying the user's access token
                    'token' => $accessToken,
                ]
            );

        $response->throw();

        return $response->json();
    }

    /**
     * @param  array<int,string>  $scopes
     * @param  array<int,string>  $scopesForToken
     * @return void
     */
    protected function checkScopes(array $scopes, array $scopesForToken)
    {
        $misingScopes = collect($scopes)->diff($scopesForToken);
        throw_if($misingScopes->isNotEmpty(), InvalidAccessTokenException::class, 'Missing the following required scopes: '.$misingScopes->join(', '));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$scopes): Response
    {
        if (app()->environment('production')) {
            $authorization = $request->header('X-Oauth-Token');

            throw_if(strlen($authorization) === 0, InvalidInputException::class, 'No Authorization header present');

            $receivedAccessToken = preg_replace('/^Bearer (.*?)$/', '$1', $authorization);

            throw_if(strlen($receivedAccessToken) <= 1, InvalidInputException::class, 'No Bearer token in the Authorization header present');

            // Now verify the user provided access token
            try {
                $result = $this->getIntrospect($receivedAccessToken);

                throw_if(! $result['active'], InvalidAccessTokenException::class, 'Invalid token!');

                if ($scopes !== null && array_key_exists('scope', $result)) {
                    if (! \is_array($scopes)) {
                        $scopes = [$scopes];
                    }

                    $scopesForToken = \explode(' ', $result['scope']);

                    $this->checkScopes($scopes, $scopesForToken);
                }

            } catch (RequestException $e) {
                // if ($e->response) {
                $result = $e->response->json();

                if (isset($result['error'])) {
                    throw new InvalidAccessTokenException($result['error']['title'] ?? 'Invalid token!');
                } else {
                    throw new InvalidAccessTokenException('Invalid token!');
                }
                // } else {
                //     throw new InvalidAccessTokenException($e);
                // }
            }
        }

        return $next($request);
    }
}
