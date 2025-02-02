<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tour extends Model
{
    use HasFactory;
    use Filterable;

    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'price'];

    public function travels()
    {
        return $this->belongsTo(Travel::class, 'travel_id');
    }

    public function price(): Attribute
    {
        return Attribute::make(get: static fn ($value) => $value / 100, set: static fn ($value) => $value * 100);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
