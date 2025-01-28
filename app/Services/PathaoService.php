<?php

namespace App\Services;

use App\Data\Dto\Pathao\CreateNewOrderRequestData;
use App\Data\Dto\Pathao\CreateNewOrderResponseData;
use App\Data\Dto\Pathao\CreateNewStoreRequestData;
use App\Data\Dto\Pathao\CreateNewStoreResponseData;
use App\Data\Dto\Pathao\GetAreasByZoneResponseData;
use App\Data\Dto\Pathao\GetCitiesResponseData;
use App\Data\Dto\Pathao\GetZonesByCityResponseData;
use App\Http\Integrations\Pathao\PathaoConnector;
use App\Http\Integrations\Pathao\Requests\CreateNewOrderRequest;
use App\Http\Integrations\Pathao\Requests\CreateNewStoreRequest;
use App\Http\Integrations\Pathao\Requests\GetAreasByZoneRequest;
use App\Http\Integrations\Pathao\Requests\GetCitiesRequest;
use App\Http\Integrations\Pathao\Requests\GetZonesByCityRequest;

class PathaoService
{
    public function __construct(private PathaoConnector $connector)
    {
    }

    public function createNewStore(CreateNewStoreRequestData $data): CreateNewStoreResponseData
    {
        $response = $this->connector->send(new CreateNewStoreRequest($data));

        /** @var CreateNewStoreResponseData */
        return $response->dtoOrFail();
    }

    public function createNewOrder(CreateNewOrderRequestData $data): CreateNewOrderResponseData
    {
        $response = $this->connector->send(new CreateNewOrderRequest($data));

        /** @var CreateNewOrderResponseData */
        return $response->dtoOrFail();
    }

    public function getCities(): GetCitiesResponseData
    {
        $response = $this->connector->send(new GetCitiesRequest());

        /** @var GetCitiesResponseData */
        return $response->dtoOrFail();
    }

    public function getZonesByCity(int $cityId): GetZonesByCityResponseData
    {
        $response = $this->connector->send(new GetZonesByCityRequest($cityId));

        /** @var GetZonesByCityResponseData */
        return $response->dtoOrFail();
    }

    public function getAreasByZone(int $zoneId): GetAreasByZoneResponseData
    {
        $response = $this->connector->send(new GetAreasByZoneRequest($zoneId));

        /** @var GetAreasByZoneResponseData */
        return $response->dtoOrFail();
    }
}
