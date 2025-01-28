<?php

namespace App\Http\Controllers;

use App\Constants\GateNames;
use App\Data\Dto\GenerateSubscriberTokenData;
use App\Data\Resources\UserData;
use App\Facades\Livestream as FacadesLivestream;
use App\Models\Livestream;

class GetLivestreamSubscriberTokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Livestream $livestream)
    {
        $this->authorize(GateNames::GET_LIVESTREAM_SUBSCRIBER_TOKEN->value, $livestream);

        $userId = auth()->id() ?? 'public';
        $displayName = auth()->user()?->name ?? 'public';

        $roomName = $livestream->getRoomName();

        $data = new GenerateSubscriberTokenData(
            roomName: $roomName,
            identity: $userId,
            displayName: $displayName,
            isPublic: $userId === 'public',
            userData: auth()->user() ? UserData::from(auth()->user()) : null
        );

        $roomToken = FacadesLivestream::generateSubscriberToken($data);

        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
