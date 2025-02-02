<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tour;
use App\Models\Travel;

interface TourServiceInterface
{
    public function filters(Travel $travel, array $request);

    public function store(Travel $travel, array $data): Tour;

    public function destroy(Tour $tour);

    public function update(Tour $tour, array $data): void;
}
