<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\ImagePath;
use App\Models\Tour;

final readonly class CommentService implements CommentInterface
{
    public function __construct(
        private ImageInterface $image,
    ) {
    }

    public function store(Tour $tour, array $data)
    {
        $user = auth()
            ->user();
        $comment = $user?->comments()
            ->create([
                'text' => $data['text'],
                'tour_id' => $tour->id,
            ]);
        if (isset($data['images'])) {
            $this->image->save(model: $comment, files: $data['images'], path: ImagePath::COMMENT_PATH);
        }

    }
}
