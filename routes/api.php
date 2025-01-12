<?php

use App\Http\Controllers\Api\V1\Admin\TravelController as AdminTravelController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'travels'], function () {
    Route::get('/', [TravelController::class, 'index'])->name('travels.index');
    Route::get('{travel:slug}', [TravelController::class, 'show'])->name('travels.show');

    Route::group(['prefix' => '{travel:slug}/tours'], function () {
        Route::get('/', [TourController::class, 'index'])->name('tours.index');
        Route::get('{tour}', [TourController::class, 'show'])->name('tours.show');
    });
});


Route::group(['prefix' => 'auth', 'middleware' => ['throttle:api'], 'as' => 'auth.'], function () {
    Route::post('register', RegisterController::class)->name('register')->middleware('guest');
    Route::post('login', LoginController::class)->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth:sanctum');
});

Route::group(['prefix' => 'admin', 'middleware' => ['throttle:api', 'auth:sanctum', 'role:ADMIN'], 'as' => 'admin.'], static function () {
    Route::post('travels/store', [AdminTravelController::class, 'store'])->name('travels.store');
    Route::put('travels/{travel:id}', [AdminTravelController::class, 'update'])->name('travels.update');
    Route::delete('travels/{travel:id}', [AdminTravelController::class, 'destroy'])->name('travels.destroy');
});
