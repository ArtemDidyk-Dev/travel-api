<?php

declare(strict_types=1);

namespace App\Observers;

use App\Mail\CommentPublished;
use App\Models\Comment;
use Illuminate\Support\Facades\Mail;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        $oldData = $comment->getOriginal();
        $newData = $comment->getAttributes();
        if ($oldData['is_public'] === 0 && $newData['is_public'] === '1') {
            $tour = $comment->tour;
            $tourLink = route('tours.show', [
                'tour' => $tour,
                'travel' => $tour->travels,
            ]);
            Mail::to($comment->user->email)->queue(new CommentPublished($tourLink));
        }
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        //
    }
}
