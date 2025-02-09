<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Tour;
use App\Models\Travel;
use App\Services\CommentInterface;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentInterface $commentService,
    ) {
    }

    public function __invoke(Travel $travel, Tour $tour, StoreCommentRequest $request)
    {
        if ($tour->travels->isNot($travel)) {
            abort(404);
        }
        try {
            $this->commentService->store($tour, $request->validated());
            return response()->json([
                'message' => 'Comment added, stay tuned for updates',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }
}
