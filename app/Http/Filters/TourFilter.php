<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class TourFilter extends AbstractFilter
{
    public const START_DATE = 'start_date';

    public const END_DATE = 'end_date';

    public const PRICE_FROM = 'price_from';

    public const PRICE_TO = 'price_to';

    public function startDate(Builder $builder, $value): void
    {
        $builder->where(self::START_DATE, '>=',  $value);
    }

    public function endDate(Builder $builder, $value): void
    {
        $builder->where(self::END_DATE, '<=', $value);
    }

    public function priceFrom(Builder $builder, $value): void
    {
        $builder->where('price', '>=', $value * 100);
    }

    public function priceTo(Builder $builder, $value): void
    {
        $builder->where('price', '<=', $value * 100);
    }

    protected function getCallbacks(): array
    {
        return [
            self::START_DATE => [$this, 'startDate'],
            self::END_DATE => [$this, 'endDate'],
            self::PRICE_FROM => [$this, 'priceFrom'],
            self::PRICE_TO => [$this, 'priceTo'],
        ];
    }
}
