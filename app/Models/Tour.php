<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasFactory;

    public function travels(): HasMany
    {
        return $this->hasMany(Travel::class);
    }

    public function price(): Attribute
    {
        return Attribute::make(get: static fn ($value) => $value / 100, set: static fn ($value) => $value * 100);
    }
}
