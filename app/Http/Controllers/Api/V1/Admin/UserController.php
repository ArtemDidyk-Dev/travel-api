<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'User Management', description: 'Manage users')]
class UserController extends Controller
{
    #[OA\Get(
        path: '/api/v1/admin/users',
        description: 'Show Users Only visible to users with the Admin or Editor roles.',
        summary: 'Users get',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['User Management']
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'The page number to retrieve',
        in: 'query',
        required: false,
        example: '1'
    )]
    #[OA\Response(
        response: 200,
        description: 'Successfully retrieved list of users',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 7),
                            new OA\Property(property: 'name', type: 'string', example: 'Dr. Alivia Stamm'),
                            new OA\Property(property: 'email', type: 'string', example: 'johnathon78@example.org'),
                            new OA\Property(
                                property: 'roles',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 3),
                                        new OA\Property(property: 'name', type: 'string', example: 'USER'),
                                    ],
                                    type: 'object'
                                )
                            ),
                            new OA\Property(property: 'created_at', type: 'string', example: '2025-02-09 19:22:56'),
                            new OA\Property(property: 'updated_at', type: 'string', example: '2025-02-09 19:22:56'),
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(
                    property: 'links',
                    properties: [
                        new OA\Property(
                            property: 'first',
                            type: 'string',
                            example: 'http://travel-api.localhost/api/v1/admin/users?page=1'
                        ),
                        new OA\Property(
                            property: 'last',
                            type: 'string',
                            example: 'http://travel-api.localhost/api/v1/admin/users?page=2'
                        ),
                        new OA\Property(property: 'prev', type: 'string', example: null, nullable: true),
                        new OA\Property(
                            property: 'next',
                            type: 'string',
                            example: 'http://travel-api.localhost/api/v1/admin/users?page=2'
                        ),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'from', type: 'integer', example: 1),
                        new OA\Property(property: 'last_page', type: 'integer', example: 2),
                        new OA\Property(
                            property: 'links',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'url', type: 'string', example: null),
                                    new OA\Property(property: 'label', type: 'string', example: '&laquo; Previous'),
                                    new OA\Property(property: 'active', type: 'boolean', example: false),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'path',
                            type: 'string',
                            example: 'http://travel-api.localhost/api/v1/admin/users'
                        ),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                        new OA\Property(property: 'to', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 16),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')],
            type: 'object'
        )
    )]
    public function index(): AnonymousResourceCollection
    {
        $users = User::with(['roles'])->paginate();
        return UserResource::collection($users);
    }

    #[OA\Get(
        path: '/api/v1/admin/users/{id}',
        description: 'Get details of a specific user. Only visible to users with the Admin or Editor roles.',
        summary: 'Get user by ID',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['User Management']
    )]
    #[OA\Parameter(name: 'id', description: 'ID of the user to retrieve', in: 'path', required: true, example: 7)]
    #[OA\Response(
        response: 200,
        description: 'Successfully retrieved user details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 7),
                new OA\Property(property: 'name', type: 'string', example: 'Dr. Alivia Stamm'),
                new OA\Property(property: 'email', type: 'string', example: 'johnathon78@example.org'),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 3),
                            new OA\Property(property: 'name', type: 'string', example: 'USER'),
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(property: 'created_at', type: 'string', example: '2025-02-09 19:22:56'),
                new OA\Property(property: 'updated_at', type: 'string', example: '2025-02-09 19:22:56'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'User not found.')]
        )
    )]
    public function show(User $user): UserResource
    {
        $user->load('roles');
        return UserResource::make($user);
    }

    #[OA\Delete(
        path: '/api/v1/admin/users/{id}',
        description: 'Delete a specific user. Only accessible to users with Admin or Editor roles.',
        summary: 'Delete user by ID',
        security: [[
            'sanctum' => [],
        ]],
        tags: ['User Management']
    )]
    #[OA\Parameter(name: 'id', description: 'ID of the user to delete', in: 'path', required: true, example: 7)]
    #[OA\Response(response: 204, description: 'User successfully deleted')]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'User not found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'User not found.')]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error',
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
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }
}
