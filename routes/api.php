<?php

use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Support\Facades\Route;

Route::resource('travels', TravelController::class);
Route::resource('travels/{travel:slug}/tours', TourController::class);
