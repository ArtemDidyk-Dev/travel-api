<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Travel;

interface TourServiceInterface
{
    public function filters(Travel $travel, array $request);
}
