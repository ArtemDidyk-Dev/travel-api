<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\PublicScope;
use App\Observers\CommentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([CommentObserver::class])]
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

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public static function getTableName(): string
    {
        return with(new static())
            ->getTable();
    }
}
