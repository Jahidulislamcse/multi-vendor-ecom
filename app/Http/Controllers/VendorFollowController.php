<?php

namespace App\Http\Controllers;

use App\Constants\GateNames;
use App\Data\Resources\UserData;
use App\Data\Resources\VendorData;
use App\Models\Vendor;
use Spatie\LaravelData\PaginatedDataCollection;

class VendorFollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Vendor $vendor): PaginatedDataCollection
    {
        return new PaginatedDataCollection(UserData::class, $vendor->followers()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Vendor $vendor)
    {
        $this->authorize(GateNames::FOLLOW_VENDOR->value, $vendor);

        $vendor->followers()->attach([auth()->id()]);

        $vendor->loadCount('followers', 'products');

        return VendorData::from($vendor)->include('followers_count', 'products_count');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $this->authorize(GateNames::UNFOLLOW_VENDOR->value, $vendor);
        $vendor->followers()->detach([auth()->id()]);

        $vendor->loadCount('followers', 'products');

        return VendorData::from($vendor)->include('followers_count', 'products_count');
    }
}
