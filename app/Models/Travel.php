<?php

declare(strict_types=1);

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Travel extends Model
{
    use Sluggable;
    use HasFactory;

    protected $table = 'travels';

    protected $fillable = ['name', 'slug', 'description', 'is_public', 'number_of_days'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function numberOfNights(): Attribute
    {
        return Attribute::make(get: static fn ($value, $attributes) => $attributes['number_of_days'] - 1);
    }

    public function scopeIsPublic(Builder $builder): void
    {
        $builder->where('is_public', true);
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }
}
