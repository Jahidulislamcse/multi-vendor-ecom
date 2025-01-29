<?php

use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('role:user')->group(function () {
    Route::get('/user/dashboard', function () {
        return 'User Dashboard';
    });
});

Route::middleware('role:vendor')->group(function () {
    Route::get('/vendor/dashboard', function () {
        return view('vendor.vendor_dashboard');
    });
});

Route::middleware('role:admin')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.index');
        })->name('dashboard');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingController::class, 'Index'])->name('index');
            Route::post('/', [AdminSettingController::class, 'Update'])->name('update');
        });
    });
});

require __DIR__ . '/auth.php';
