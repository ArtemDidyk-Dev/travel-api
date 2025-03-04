<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Tour;
use App\Models\Travel;
use App\Services\CommentInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentInterface $commentService,
    ) {
    }

    #[OA\Post(
        path: '/api/v1/travels/{travel}/tours/{tour}/comments',
        operationId: 'storeComment',
        description: 'Stores a comment for a specific tour that belongs to a travel, with optional image attachments.',
        summary: 'Add a new comment to a tour',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Comments'],
    )]
    #[OA\Parameter(name: 'travel', description: 'Slug travel', in: 'path', required: true, example: 'aut-totam')]
    #[OA\Parameter(
        name: 'tour',
        description: 'Tour ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['text'],
                properties: [
                    new OA\Property(
                        property: 'text',
                        type: 'string',
                        maxLength: 5000,
                        minLength: 1,
                        example: 'This tour was amazing!'
                    ),
                    new OA\Property(
                        property: 'images[]',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'binary')
                    ),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Comment successfully created',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Comment added, stay tuned for updates'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Tour does not belong to the specified travel',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Not found')]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation failed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(
                    property: 'errors',
                    properties: [
                        new OA\Property(
                            property: 'text',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'The text field is required')
                        ),
                        new OA\Property(
                            property: 'images.0',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'The image must be a file of type: jpg, jpeg, png'
                            )
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'An error occurred. Please try again later.'
                ),
            ]
        )
    )]
    public function __invoke(Travel $travel, Tour $tour, StoreCommentRequest $request): JsonResponse
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
