<?php

namespace App\Http\Controllers;

use App\Constants\GateNames;
use App\Data\Dto\GeneratePublisherTokenData;
use App\Facades\Livestream as LivestreamService;
use App\Models\Livestream;

class GetLivestreamPublisherTokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Livestream $livestream)
    {
        $this->authorize(GateNames::GET_LIVESTREAM_PUBLISHER_TOKEN->value, $livestream);

        $vendorName = $livestream->vendor->name;

        $roomName = $livestream->getRoomName();

        $data = new GeneratePublisherTokenData(
            roomName: $roomName,
            identity: $vendorName,
            displayName: $vendorName
        );

        $roomToken = LivestreamService::generatePublisherToken($data);

        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
