<?php

namespace App\Http\Controllers;

use App\Constants\GateNames;
// use App\Constants\SupportedShippingMethods;
use App\Data\Dto\CreateVendorData;
use App\Data\Dto\UpdateVendorData;
use App\Data\Resources\VendorData;
// use App\Jobs\SubmitVendorDataToPathaoToCreateStore;
use App\Models\User;
use App\Models\Vendor;
use App\Queries\VendorIndexQuery;
use DB;
use Spatie\LaravelData\PaginatedDataCollection;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(VendorIndexQuery $vendorQuery): PaginatedDataCollection
    {
        $vendors = $vendorQuery
            ->paginate();

        return (new PaginatedDataCollection(VendorData::class, $vendors))->include('followers_count', 'products_count');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateVendorData $createVendorData): VendorData
    {
        return DB::transaction(function () use ($createVendorData) {
            /** @var User $user */
            $user = auth()->user();

            /** @var Vendor */
            $newVendor = $user->vendors()->create($createVendorData->toArray());

            // $newVendor->deliveryProviderAccounts()->firstOrCreate(
            //     [
            //         'provider_name' => SupportedShippingMethods::PATHAO->value,
            //     ],
            //     [
            //         'data' => [
            //             'city' => $createVendorData->city,
            //             'area' => $createVendorData->area,
            //             'zone' => $createVendorData->zone,
            //         ],
            //     ]
            // );

            // SubmitVendorDataToPathaoToCreateStore::dispatch($newVendor);

            return VendorData::from($newVendor)->include('followers_count', 'products_count');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor): VendorData
    {
        $vendor->loadCount('followers', 'products');

        return VendorData::from($vendor)->include('followers_count', 'products_count');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendorData $updateVendorData, Vendor $vendor): VendorData
    {
        $this->authorize(GateNames::UPDATE_VENDOR->value, $vendor);

        $vendor->update($updateVendorData->toArray());

        if (is_string($updateVendorData->profilePicture)) {
            $vendor->addAllMediaFromTokens($updateVendorData->profilePicture, 'profile');
        }

        if (is_string($updateVendorData->coverPicture)) {
            $vendor->addAllMediaFromTokens($updateVendorData->coverPicture, 'cover');
        }

        $vendor->loadCount('followers', 'products');

        return VendorData::from($vendor)->include('followers_count', 'products_count');
    }
}
