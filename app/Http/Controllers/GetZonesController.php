<?php

namespace App\Http\Controllers;

use App\Facades\Pathao;

class GetZonesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $cityId)
    {
        return Pathao::getZonesByCity($cityId);
    }
}
