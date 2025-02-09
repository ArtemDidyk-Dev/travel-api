<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\TourFilterRequests;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Models\Travel;
use App\Services\TourServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TourController extends Controller
{
    public function __construct(
        private readonly TourServiceInterface $tourService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Travel $travel, TourFilterRequests $filterRequests): AnonymousResourceCollection
    {
        $tours = $this->tourService->filters($travel, $filterRequests->all());
        return TourResource::collection($tours);
    }

    public function show(Travel $travel, Tour $tour): TourResource
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        $tour->load(['images', 'comments.images', 'comments.user']);
        return new TourResource($tour);
    }
}
