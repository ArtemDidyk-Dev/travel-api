<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\PublicScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ScopedBy([PublicScope::class])]
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['text', 'user_id', 'is_public', 'tour_id'];

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
