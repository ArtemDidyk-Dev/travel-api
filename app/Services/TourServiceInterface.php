<?php

namespace App\Services;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Http\FormRequest;

interface TourServiceInterface
{
    public function filters(HasMany $travel, array $request);
}
