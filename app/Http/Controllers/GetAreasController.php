<?php

namespace App\Http\Controllers;

use App\Facades\Pathao;
use Illuminate\Http\Request;

class GetAreasController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $zoneId)
    {
        return Pathao::getAreasByZone($zoneId);
    }
}
