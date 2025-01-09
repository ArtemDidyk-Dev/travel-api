<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTravelRequest;
use App\Http\Requests\UpdateTravelRequest;
use App\Http\Resources\TravelResource;
use App\Models\Travel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TravelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {

        $travels = Travel::paginate();
        return TravelResource::collection($travels);
    }

    public function store(StoreTravelRequest $request)
    {
        //
    }

    public function show(Travel $travel)
    {
        //
    }

    public function update(UpdateTravelRequest $request, Travel $travel)
    {
        //
    }

    public function destroy(Travel $travel)
    {
        //
    }
}
