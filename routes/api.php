<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\GetAreasController;
use App\Http\Controllers\GetAuthCartController;
use App\Http\Controllers\GetCitiesController;
use App\Http\Controllers\GetLivestreamPublisherTokenController;
use App\Http\Controllers\GetLivestreamSubscriberTokenController;
use App\Http\Controllers\GetProductsController;
use App\Http\Controllers\GetZonesController;
use App\Http\Controllers\HandleFailedOrderController;
use App\Http\Controllers\InitiatePaymentController;
use App\Http\Controllers\LivestreamController;
use App\Http\Controllers\LivestreamProductController;
use App\Http\Controllers\MakeCartCheckoutController;
// use App\Http\Controllers\MakeOrderPick1RequestToPathaoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorFollowController;
use App\Http\Controllers\VendorProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'verifyaccesstoken'], function () {
    Route::post('auth/otp/email', [AuthController::class, 'getEmailOtp'])
        ->name('otp.email')
        ->middleware('throttle:login');

    Route::post('auth/login/otp/email', [AuthController::class, 'loginViaEmailOtp'])
        ->name('otp-login.email')
        ->middleware('throttle:login');

    Route::post('auth/login/otp/phone', [AuthController::class, 'verifyPhoneOtp'])
        ->name('otp-login.phone');

    Route::apiResource('vendors', VendorController::class)->except(['destroy']);
    Route::post('vendors/{vendor}/follows', [VendorFollowController::class, 'store'])
        ->name('vendor-follows.store');
    Route::get('vendors/{vendor}/follows', [VendorFollowController::class, 'index'])
        ->name('vendor-follows.index');
    Route::delete('vendors/{vendor}/follows', [VendorFollowController::class, 'destroy'])
        ->name('vendor-follows.destroy');
    Route::apiResource('vendors/{vendor}/products', VendorProductController::class)->except(['index', 'destroy'])->names('vendor-products');
    Route::get('products', GetProductsController::class)->name('products');
    Route::apiResource('product-types', ProductTypeController::class)
        ->names('product-types')
        ->only([
            'index',
            'show',
        ]);

    Route::apiResource('livestreams', LivestreamController::class)->except(['destroy']);
    Route::get('livestreams/{livestream}/publisher-token', GetLivestreamPublisherTokenController::class)
        ->middleware('auth:sanctum')
        ->name('livestreams.get-publisher-token');
    Route::get('livestreams/{livestream}/subscriber-token', GetLivestreamSubscriberTokenController::class)
        ->name('livestreams.get-subscriber-token');
    Route::post('livestreams/{livestream}/products', [LivestreamProductController::class, 'store'])
        ->name('livestream-products.store');
    Route::delete('livestreams/{livestream}/products', [LivestreamProductController::class, 'destroy'])
        ->name('livestream-products.destroy');

    Route::apiResource('carts', CartController::class)->except('index');
    Route::post('carts/{cart}/checkout', MakeCartCheckoutController::class)->name('carts.checkout');
    Route::get('auth/active-cart', GetAuthCartController::class)->middleware('auth:sanctum')
        ->name('auth.active-cart');

    Route::post('payments/initiate/{cart}/{supportedPaymentMethod}', InitiatePaymentController::class)
        ->name('payments.initiate');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show')->middleware('auth:sanctum');
    Route::put('update-email', [ProfileController::class, 'updateEmail'])->name('email.update')->middleware('auth:sanctum');
    Route::put('update-phone', [ProfileController::class, 'updatePhone'])->name('phone.update')->middleware('auth:sanctum');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth:sanctum');

    Route::apiResource('orders', OrderController::class)->middleware('auth:sanctum')->names('orders')->except(['store', 'destroy', 'update']);
    // Route::post('orders/{order}/pickup/pathao', MakeOrderPickupRequestToPathaoController::class)->middleware('auth:sanctum')->name('orders.pickup-pathao');
    Route::get('cities', GetCitiesController::class)->name('cities.index');
    Route::get('cities/{city_id}/zones', GetZonesController::class)->name('zones.index');
    Route::get('zones/{zone_id}/areas', GetAreasController::class)->name('areas.index');

    // TODO: endpoint for withdraw request
    Route::put('orders/{order}/handle-failure', HandleFailedOrderController::class)->middleware('auth:sanctum')->name('orders.handle-failure');
});
