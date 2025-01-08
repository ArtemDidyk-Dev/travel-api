<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;

Route::resource('travels', TravelController::class);
Route::resource('travels/{travel:slug}/tours', TourController::class);

Route::group(['prefix' => 'auth', 'middleware' => ['throttle:api', 'guest'], 'as'=> 'auth.'], function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});
