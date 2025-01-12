<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTravelRequest;
use App\Http\Requests\Admin\UpdateTravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;
use Illuminate\Http\JsonResponse;

class TravelController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTravelRequest $request): TravelResource|JsonResponse
    {
        try {
            $travel = Travel::create($request->validated());
            return (new TravelResource($travel->fresh()))->response()
                ->setStatusCode(201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTravelRequest $request, Travel $travel): TravelResource|JsonResponse
    {
        try {
            $travel->update($request->validated());
            return new TravelResource($travel->fresh());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Travel $travel): JsonResponse
    {
        try {
            $travel->delete();
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }
}
