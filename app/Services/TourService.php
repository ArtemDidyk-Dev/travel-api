<?php

namespace App\Services;

use App\Http\Filters\TourFilter;
use App\Models\Travel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Http\FormRequest;

class TourService implements TourServiceInterface
{

    public function filters(HasMany $travel, array $request)
    {
        $filters = new TourFilter(array_filter($request));
        return $travel->filter($filters)->orderBy($request['sort_by'], $request['sort_order'])->paginate();
    }
}
