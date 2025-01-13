<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\TourController as AdminTourController;
use App\Http\Controllers\Api\V1\Admin\TravelController as AdminTravelController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'travels',
], static function () {
    Route::get('/', [TravelController::class, 'index'])->name('travels.index');
    Route::get('{travel:slug}', [TravelController::class, 'show'])->name('travels.show');

    Route::group([
        'prefix' => '{travel:slug}/tours',
    ], static function () {
        Route::get('/', [TourController::class, 'index'])->name('tours.index');
        Route::get('{tour}', [TourController::class, 'show'])->name('tours.show');
    });
});

Route::group([
    'prefix' => 'auth',
    'middleware' => ['throttle:api'],
    'as' => 'auth.',
], static function () {
    Route::post('register', RegisterController::class)->name('register')->middleware('guest');
    Route::post('login', LoginController::class)->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth:sanctum');
});

Route::group(
    [
        'prefix' => 'admin',
        'middleware' => ['throttle:api', 'auth:sanctum', 'role:ADMIN'],
        'as' => 'admin.',
    ],
    static function () {
        Route::group([
            'prefix' => 'travels',
        ], static function () {
            Route::post('store', [AdminTravelController::class, 'store'])->name('travels.store');
            Route::group([
                'prefix' => '{travel:id}',
            ], static function () {
                Route::put('/', [AdminTravelController::class, 'update'])->name('travels.update');
                Route::delete('/', [AdminTravelController::class, 'destroy'])->name('travels.destroy');
                Route::group([
                    'prefix' => 'tours',
                ], static function () {
                    Route::post('store', [AdminTourController::class, 'store'])->name('tours.store');
                    Route::put('{tour}', [AdminTourController::class, 'update'])->name('tours.update');
                    Route::delete('{tour}', [AdminTourController::class, 'destroy'])->name('tours.destroy');
                });
            });
        });
    }
);
