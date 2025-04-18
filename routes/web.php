<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSliderController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('role:user')->group(function () {
    Route::get('/user/dashboard', function () {
        return 'User Dashboard';
    });
});

Route::middleware('role:vendor')->group(function () {});

Route::prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', function () {
        return view('vendor.index');
    })->name('dashboard');
    Route::resource('products', VendorProductController::class);
    Route::get('product-image-delete/{id}', [VendorProductController::class, 'ImageDelete'])->name('product-image-delete');
    Route::get('product-stock-delete/{id}', [VendorProductController::class, 'StockDelete'])->name('product-stock-delete');
    Route::get('/get-tags', [TagController::class, 'getTags'])->name('get.tags');
});

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [VendorProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [VendorProfileController::class, 'update'])->name('profile.update');
        Route::controller(VendorOrderController::class)->group(function () {
            Route::prefix('order')->name('order.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/pending', 'PendingOrder')->name('pending');
                Route::get('/details/{id}', 'orderDetails')->name('details');
                Route::get('/confirmed', 'ConfirmedOrder')->name('confirmed');
                Route::get('/processing', 'ProcessingOrder')->name('processing');
                Route::get('/delivered', 'DeliveredOrder')->name('delivered');
                Route::get('/completed', 'completedOrder')->name('completed');
                Route::get('/cancled', 'CancledOrder')->name('cancled');
                Route::get('/pending/confirm/{order_id}', 'PendingToConfirm')->name('pending-confirm');
                Route::get('/confirm/processing/{order_id}', 'ConfirmToProcess')->name('confirm-processing');
                Route::get('/pending/cancel/{order_id}', 'PendingToCancel')->name('pending-cancel');
                Route::get('/processing/delivered/{order_id}', 'ProcessToDelivered')->name('processing-delivered');
                Route::get('/invoice/download/{order_id}', 'AdminInvoiceDownload')->name('invoice.download');
            });
        });
        Route::get('/payment/request', [PaymentController::class, 'request'])->name('payment.request');
        Route::post('/money/withdraw', [PaymentController::class, 'storeWithdrawRequest'])->name('money.withdraw');
        Route::get('/payment/history', [PaymentController::class, 'paymentHistory'])->name('payment.history');
});


Route::middleware(['auth', 'role:admin,vendor'])->group(function () {});
Route::get('/create-vendor-account', [ProfileController::class, 'createVendor'])->name('create.vendor.account');
Route::post('vendor/application', [UserController::class, 'application'])->name('vendor.application');

Route::middleware('role:admin')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('products', AdminProductController::class);
        Route::controller(AdminOrderController::class)->group(function () {
            Route::prefix('order')->name('order.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/pending', 'PendingOrder')->name('pending');
                Route::get('/details/{id}', 'orderDetails')->name('details');
                Route::get('/confirmed', 'ConfirmedOrder')->name('confirmed');
                Route::get('/processing', 'ProcessingOrder')->name('processing');
                Route::get('/delivered', 'DeliveredOrder')->name('delivered');
                Route::get('/completed', 'completedOrder')->name('completed');
                Route::get('/cancled', 'CancledOrder')->name('cancled');
                Route::get('/delivered/completed/{order_id}', 'DeliveredToCompleted')->name('delivered-completed');
                Route::get('/invoice/download/{order_id}', 'AdminInvoiceDownload')->name('invoice.download');
            });
        });

        Route::resource('sliders', AdminSliderController::class);
        Route::get('get-tags', [TagController::class, 'getTags'])->name('getTags');


        Route::get('user/list', [UserController::class, 'userList'])->name('user.list');
        Route::post('user/store', [UserController::class, 'store'])->name('user.store');
        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingController::class, 'Index'])->name('index');
            Route::post('/', [AdminSettingController::class, 'Update'])->name('update');
        });
        Route::get('/payment/requests', [PaymentController::class, 'PaymentRequests'])->name('payment.requests');
        Route::get('/payment/history', [PaymentController::class, 'AdminPaymentHistory'])->name('payment.history');
        Route::put('/payment/update/{bill}', [PaymentController::class, 'update'])->name('payment.update');
    });
});

require __DIR__ . '/auth.php';
