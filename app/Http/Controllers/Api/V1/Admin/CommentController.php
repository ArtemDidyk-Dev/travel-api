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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comment Management', description: 'Manage Comment')]
class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService,
        private readonly ImageInterface $imageService,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/admin/comments',
        description: 'Shows all comments of the resource',
        summary: 'Comments get',
        tags: ['Comment Management']
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'The page number to retrieve',
        in: 'query',
        required: false,
        example: '1'
    )]
    #[OA\Response(
        response: '200',
        description: 'Comments get',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 11),
                            new OA\Property(
                                property: 'text',
                                type: 'string',
                                example: 'Deserunt ut explicabo quis expedita. Dolorem fuga accusamus qui nemo minima.'
                            ),
                            new OA\Property(property: 'is_public', type: 'boolean', example: true),
                            new OA\Property(
                                property: 'images',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                        new OA\Property(
                                            property: 'url',
                                            type: 'string',
                                            example: 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png'
                                        ),
                                    ],
                                    type: 'object'
                                ),
                                example: [
                                    [
                                        'id' => 2,
                                        'url' => 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png',
                                    ],
                                    [
                                        'id' => 3,
                                        'url' => 'http://travel-api.localhost/storage/public/images/comments/anotherimage.png',
                                    ],
                                ]
                            ),
                            new OA\Property(property: 'created_at', type: 'string', example: '2025 Feb 11'),
                            new OA\Property(property: 'user', type: 'string', example: 'Dr. Alivia Stamm'),
                            new OA\Property(
                                property: 'tour',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                    new OA\Property(property: 'name', type: 'string', example: 'Fuga ipsa illo.'),
                                    new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                                    new OA\Property(property: 'end_date', type: 'string', example: '2025-02-19'),
                                    new OA\Property(property: 'price', type: 'string', example: '590.45'),
                                ],
                                type: 'object'
                            ),
                        ],
                        type: 'object'
                    ),
                ),
                new OA\Property(
                    property: 'links',
                    properties: [
                        new OA\Property(
                            property: 'first',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/v1/comments?page=1'
                        ),
                        new OA\Property(
                            property: 'last',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/v1/comments?page=4'
                        ),
                        new OA\Property(property: 'prev', type: 'string', example: null, nullable: true),
                        new OA\Property(
                            property: 'next',
                            type: 'string',
                            example: 'http://api-laravel.localhost/api/v1/comments?page=2'
                        ),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'from', type: 'integer', example: 1),
                        new OA\Property(property: 'last_page', type: 'integer', example: 4),
                        new OA\Property(property: 'per_page', type: 'integer', example: 5),
                        new OA\Property(property: 'to', type: 'integer', example: 5),
                        new OA\Property(property: 'total', type: 'integer', example: 17),
                        new OA\Property(
                            property: 'links',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'url', type: 'string'),
                                    new OA\Property(property: 'label', type: 'string'),
                                    new OA\Property(property: 'active', type: 'boolean'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated.',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
            type: 'object'
        )
    )]
    public function index(): AnonymousResourceCollection
    {
        $comments = Comment::with(['user', 'images', 'tour'])->paginate(10);

        return CommentResource::collection($comments);
    }

    #[OA\Get(
        path: '/api/v1/admin/comments/{comment}',
        description: 'Show comment of the resource',
        summary: 'Comment get',
        tags: ['Comment Management']
    )]
    #[OA\Parameter(name: 'comment', description: 'Comment id', in: 'path', required: true, example: '5')]
    #[OA\Response(
        response: '200',
        description: 'Comment get',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 11),
                        new OA\Property(
                            property: 'text',
                            type: 'string',
                            example: 'Deserunt ut explicabo quis expedita. Dolorem fuga accusamus qui nemo minima.'
                        ),
                        new OA\Property(property: 'is_public', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 11),
                                    new OA\Property(
                                        property: 'url',
                                        type: 'string',
                                        example: 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png'
                                    ),
                                ],
                                type: 'object'
                            ),
                            example: [
                                [
                                    'id' => 11,
                                    'url' => 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png',
                                ],
                                [
                                    'id' => 12,
                                    'url' => 'http://travel-api.localhost/storage/public/images/comments/anotherimage.png',
                                ],
                            ]
                        ),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025 Feb 11'),
                        new OA\Property(property: 'user', type: 'string', example: 'Dr. Alivia Stamm'),
                        new OA\Property(
                            property: 'tour',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'Fuga ipsa illo.'),
                                new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                                new OA\Property(property: 'end_date', type: 'string', example: '2025-02-19'),
                                new OA\Property(property: 'price', type: 'string', example: '590.45'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated.',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
            type: 'object'
        )
    )]
    public function show(Comment $comment): CommentResource
    {
        $comment->load(['user', 'images', 'tour']);

        return CommentResource::make($comment);
    }

    #[OA\POST(
        path: '/api/v1/admin/comments/{comment}',
        operationId: 'updateComment',
        description: 'Update the specified comment with new data ',
        summary: 'Update a specific comment',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Comment Management'],
        parameters: [
            new OA\Parameter(
                name: 'comment',
                description: 'ID of the comment to be deleted',
                in: 'path',
                required: true,
                example: 55,
            ),
        ]
    )]
    #[OA\Post(
        path: '/api/v1/admin/comments/{comment}',
        operationId: 'updateComment',
        description: 'Update the specified comment with new data (if is_public is updated to true, a queue will be created that will send a letter by email to the author of the comment that the comment passed the check)',
        summary: 'Update a specific comment',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Comment Management'],
        parameters: [
            new OA\Parameter(
                name: 'comment',
                description: 'ID of the comment to be updated',
                in: 'path',
                required: true,
                example: 11
            ),
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['text', 'is_public'],
                properties: [
                    new OA\Property(property: '_method', type: 'string', example: 'PUT'),
                    new OA\Property(
                        property: 'text',
                        type: 'string',
                        maxLength: 5000,
                        minLength: 1,
                        example: 'This tour was amazing!'
                    ),
                    new OA\Property(property: 'is_public', type: 'boolean', example: true),
                    new OA\Property(property: 'images[2]', type: 'string', format: 'binary'),
                    new OA\Property(property: 'images[33]', type: 'string', format: 'binary'),

                ]
            )
        )
    )]
    #[OA\Response(
        response: '200',
        description: 'Comment update',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 11),
                        new OA\Property(
                            property: 'text',
                            type: 'string',
                            example: 'Deserunt ut explicabo quis expedita. Dolorem fuga accusamus qui nemo minima.'
                        ),
                        new OA\Property(property: 'is_public', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 11),
                                    new OA\Property(
                                        property: 'url',
                                        type: 'string',
                                        example: 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png'
                                    ),
                                ],
                                type: 'object'
                            ),
                            example: [
                                [
                                    'id' => 11,
                                    'url' => 'http://travel-api.localhost/storage/public/images/comments/67ade644283f63.07998705.png',
                                ],
                                [
                                    'id' => 12,
                                    'url' => 'http://travel-api.localhost/storage/public/images/comments/anotherimage.png',
                                ],
                            ]
                        ),
                        new OA\Property(property: 'created_at', type: 'string', example: '2025 Feb 11'),
                        new OA\Property(property: 'user', type: 'string', example: 'Dr. Alivia Stamm'),
                        new OA\Property(
                            property: 'tour',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'Fuga ipsa illo.'),
                                new OA\Property(property: 'start_date', type: 'string', example: '2025-02-09'),
                                new OA\Property(property: 'end_date', type: 'string', example: '2025-02-19'),
                                new OA\Property(property: 'price', type: 'string', example: '590.45'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated.',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Comment not found',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'An error occurred while deleting the Tour',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'An error occurred. Please try again later.'
                    ),
                ],
                type: 'object'
            )
        )
    )]
    public function update(UpdateCommentRequest $request, Comment $comment): CommentResource
    {
        $comment->load(['user', 'images', 'tour.travels']);
        $this->commentService->update($comment, $request->validated());
        return CommentResource::make($comment);

    }

    #[OA\Delete(
        path: '/api/v1/admin/comments/{comment}',
        operationId: 'deleteComment',
        description: 'Delete a specific comment',
        summary: 'Delete a specific  comment',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['Comment Management'],
        parameters: [
            new OA\Parameter(
                name: 'comment',
                description: 'ID of the comment to be deleted',
                in: 'path',
                required: true,
                example: 12,
            ),
        ]
    )]
    #[OA\Response(response: 204, description: 'Comment deleted successfully')]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated.',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Comment not found',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [new OA\Property(property: 'message', type: 'string', example: 'Record not found.')],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'An error occurred while deleting the Tour',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'An error occurred. Please try again later.'
                    ),
                ],
                type: 'object'
            )
        )
    )]
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

    #[OA\Delete(
        path: '/api/v1/admin/comments/{comment}/files',
        operationId: 'deleteCommentFiles',
        description: 'Delete specific comment files',
        summary: 'Delete specific files',
        security: [
            [
                'sanctum' => [],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['images'],
                properties: [
                    new OA\Property(
                        property: 'images',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2]
                    ),
                ]
            )
        ),
        tags: ['Comment Management'],
        parameters: [
            new OA\Parameter(name: 'comment', description: 'Comment id', in: 'path', required: true, example: '5'),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Files deleted successfully'),
            new OA\Response(
                response: 422,
                description: 'Validation failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
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
            ),
        ]
    )]
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
