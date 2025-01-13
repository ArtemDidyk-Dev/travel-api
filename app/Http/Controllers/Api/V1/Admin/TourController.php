<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourRequest;
use App\Http\Requests\Admin\UpdateTourRequest;
use App\Http\Resources\TourResource;
use App\Http\Resources\TravelResource;
use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Http\JsonResponse;

class TourController extends Controller
{
    public function store(Travel $travel, StoreTourRequest $request): TravelResource|JsonResponse
    {
        try {
            $tour = $travel->tours()
                ->create($request->validated());
            return TourResource::make($tour)->response()->setStatusCode(201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    public function update(Travel $travel, Tour $tour, UpdateTourRequest $request): TourResource|JsonResponse
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        try {
            $tour->update($request->validated());
            return TourResource::make($tour);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }

    }

    public function destroy(Travel $travel, Tour $tour): JsonResponse
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        try {
            $tour->delete();
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }
}
