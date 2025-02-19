<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestroyFileRequest;
use App\Http\Requests\Admin\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use App\Services\ImageInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService,
        private readonly ImageInterface $imageService,
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $comments = Comment::with(['user', 'images', 'tour'])->paginate(10);

        return CommentResource::collection($comments);
    }

    public function show(Comment $comment): CommentResource
    {
        $comment->load(['user', 'images', 'tour']);

        return CommentResource::make($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->load(['user', 'images', 'tour.travels']);
        $this->commentService->update($comment, $request->validated());
        return CommentResource::make($comment);



    }

    public function destroy(Comment $comment): JsonResponse
    {
        try {
            $comment->load(['images']);
            $this->commentService->destroy($comment);

            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyFiles(Comment $comment, DestroyFileRequest $request): JsonResponse
    {
        try {
            $this->imageService->delete($comment, $request->input('images'));
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }
}
