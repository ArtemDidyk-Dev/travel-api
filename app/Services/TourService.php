<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Filters\TourFilter;
use App\Models\Travel;

class TourService implements TourServiceInterface
{
    public function filters(Travel $travel, array $request)
    {
        $filters = new TourFilter(array_filter($request));
        return $travel
            ->tours()
            ->filter($filters)
            ->orderBy($request['sort_by'], $request['sort_order'])
            ->orderBy('start_date')
            ->paginate();
    }
}
