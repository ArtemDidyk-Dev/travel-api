<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = ['path'];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }


    public static function getTableName(): string
    {
        return with(new static)->getTable();
    }
}
