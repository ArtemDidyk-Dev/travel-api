<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;

Route::apiResource('travels', TravelController::class);
Route::apiResource('travels/{travel:slug}/tours', TourController::class);
Route::group(['prefix' => 'auth', 'middleware' => ['throttle:api'], 'as' => 'auth.'], function () {
    Route::post('register', RegisterController::class)->name('register')->middleware('guest');
    Route::post('login', LoginController::class)->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth:sanctum');
});
