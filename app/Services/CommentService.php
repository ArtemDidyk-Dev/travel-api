<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\ImagePath;
use App\Models\Comment;
use App\Models\Tour;
use Illuminate\Support\Facades\DB;

final readonly class CommentService implements CommentInterface
{
    public function __construct(
        private ImageInterface $image,
    ) {
    }

    public function store(Tour $tour, array $data): void
    {
        $user = auth()
            ->user();
        $comment = $user?->comments()
            ->create([
                'text' => $data['text'],
                'tour_id' => $tour->id,
            ]);

        if (isset($data['images'])) {
            $this->image->save(model: $comment, files: $data['images'], path: ImagePath::COMMENT_PATH, async: true);
        }

    }

    public function destroy(Comment $comment): void
    {
        $filesId = $comment->images?->pluck('id')
            ->toArray();
        if ($filesId !== []) {
            $this->image->delete($comment, $filesId);
        }
        $comment->delete();
    }

    public function update(Comment $comment, array $data): void
    {
        DB::transaction(function () use ($comment, $data) {
            $comment->update($data);
            if (isset($data['images'])) {
                $this->image->update($comment, files: $data['images'], path: ImagePath::COMMENT_PATH);
            }
            $comment->load('images');
        });
    }
}
