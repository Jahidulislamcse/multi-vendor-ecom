<?php

namespace App\Http\Controllers;

use App\Data\Dto\AddLivestreamProductData;
use App\Data\Dto\RemoveLivestreamProductData;
use App\Data\Resources\LivestreamData;
use App\Models\Livestream;

class LivestreamProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Livestream $livestream, AddLivestreamProductData $data): LivestreamData
    {
        $livestream->products()->sync($data->productIds, false);

        return LivestreamData::from($livestream);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livestream $livestream, RemoveLivestreamProductData $data): LivestreamData
    {
        $livestream->products()->detach($data->productIds);

        return LivestreamData::from($livestream);
    }
}
