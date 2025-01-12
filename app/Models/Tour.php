<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;
    use Filterable;

    public function travels()
    {
        return $this->belongsTo(Travel::class, 'travel_id');
    }

    public function price(): Attribute
    {
        return Attribute::make(get: static fn ($value) => $value / 100, set: static fn ($value) => $value * 100);
    }
}
