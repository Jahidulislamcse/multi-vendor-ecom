<?php

namespace App\Http\Middleware;

use App\Exceptions\InvalidAccessTokenException;

class VerifyOauth2AccessTokenHasAnyScope extends VerifyOauth2AccessToken
{
    protected function checkScopes($scopes, $scopesForToken)
    {
        $match = false;
        foreach ($scopes as $scope) {
            if (in_array($scope, $scopesForToken)) {
                $match = true;
                break;
            }
        }
        if (! $match) {
            throw new InvalidAccessTokenException(
                'Missing one the following scopes: '.implode(' ,', $scopes)
            );
        }
    }
}
