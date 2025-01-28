<?php

namespace App\Http\Controllers;

use App\Constants\GateNames;
use App\Constants\LivestreamStatuses;
use App\Data\Dto\CreateLivestremData;
use App\Data\Dto\UpdateLivestremData;
use App\Data\Resources\LivestreamData;
use App\Models\Livestream;
use App\Models\Vendor;
use Closure;
use Illuminate\Support\Facades\Pipeline;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\QueryBuilder\QueryBuilder;

class LivestreamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): PaginatedDataCollection
    {
        $livestreams = QueryBuilder::for(Livestream::class)
            ->paginate();

        return new PaginatedDataCollection(LivestreamData::class, $livestreams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLivestremData $createLivestremData): LivestreamData
    {
        $vendor = Vendor::find($createLivestremData->vendorId);
        $this->authorize('create-livestream', $vendor);

        /** @var Livestream */
        $newLivestream = Livestream::create($createLivestremData->toArray());

        // $newLivestream->addAllMediaFromTokens($createLivestremData->thumbnailPicture, 'thumbnail');

        return LivestreamData::from($newLivestream);
    }

    /**
     * Display the specified resource.
     */
    public function show(Livestream $livestream): LivestreamData
    {
        return LivestreamData::from($livestream);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLivestremData $updateLivestremData, Livestream $livestream)
    {
        $this->authorize(GateNames::UPDATE_LIVESTREAM->value, $livestream);

        $livestream->fill($updateLivestremData->toArray());

        Pipeline::send($updateLivestremData)
            ->through([
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if (is_string($updateLivestremData->thumbnailPicture)) {
                        $livestream->addAllMediaFromTokens($updateLivestremData->thumbnailPicture, 'thumbnail');
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if ($updateLivestremData->status === LivestreamStatuses::STARTED) {
                        $livestream->started_at = now();
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if ($updateLivestremData->status === LivestreamStatuses::FINISHED) {
                        $livestream->ended_at = now();
                    }

                    return $next($updateLivestremData);
                },
                function (UpdateLivestremData $updateLivestremData, Closure $next) use (&$livestream) {
                    if (! ($updateLivestremData->status instanceof Optional)) {
                        $livestream->setStatus($updateLivestremData->status->value);
                    }

                    return $next($updateLivestremData);
                },
            ])
            ->thenReturn();

        $livestream->save();

        return LivestreamData::from($livestream);
    }
}
