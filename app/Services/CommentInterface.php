<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tour;

interface CommentInterface
{
    public function store(Tour $tour, array $data);
}
