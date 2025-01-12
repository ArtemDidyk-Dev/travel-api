<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function apply(Builder $builder);
}
