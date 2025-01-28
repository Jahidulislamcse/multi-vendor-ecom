<?php

namespace App\Http\Integrations\Pathao\Requests;

use App\Data\Dto\Pathao\GetZonesByCityResponseData;
use Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetZonesByCityRequest extends Request implements Cacheable
{
    use HasCaching;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "/aladdin/api/v1/cities/{$this->cityId}/zone-list";
    }

    public function __construct(public readonly int $cityId)
    {

    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return GetZonesByCityResponseData::from($response->json());
    }

    public function resolveCacheDriver(): Driver
    {
        return new LaravelCacheDriver(Cache::store('redis'));
    }

    public function cacheExpiryInSeconds(): int
    {
        return 3600;
    }
}
