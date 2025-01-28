<?php

namespace App\Http\Controllers;

use App\Facades\Pathao;

class GetCitiesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return Pathao::getCities();
    }
}
