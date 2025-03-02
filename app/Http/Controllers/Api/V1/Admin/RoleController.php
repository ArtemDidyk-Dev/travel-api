<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRoles;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleServicesInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
#[OA\Tag(name: 'Roles Management', description: 'Manage user roles')]
class RoleController extends Controller
{
    public function __construct(
        readonly private RoleServicesInterface $roleServices
    ) {
    }

    #[OA\Get(
        path: '/api/v1/admin/roles',
        description: 'Retrieve a list of all roles. Only accessible to users with Admin or Editor roles.',
        summary: 'Get all roles',
        security: [['sanctum' => []]],
        tags: ['Roles Management'],
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
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'ADMIN'),
                        ],
                        type: 'object'
                    ),
                    example: [
                        [
                            'id' => 1,
                            'name' => 'ADMIN',
                        ],
                        [
                            'id' => 2,
                            'name' => 'EDITOR',
                        ],
                        [
                            'id' => 3,
                            'name' => 'USER',
                        ],
                    ]

                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
            ]
        )
    )]
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(Role::all());
    }


    #[OA\Post(
        path: '/api/v1/admin/roles/users/{user}/add',
        description: 'Assign roles to a user. Only accessible to Admins.',
        summary: 'Assign roles to a user',
        security: [['sanctum' => []]],
        tags: ['Roles Management'],
    )]
    #[OA\Parameter(
        name: 'user',
        description: 'User ID to whom roles will be assigned',
        in: 'path',
        required: true,
        example: 12
    )]
    #[OA\RequestBody(
        description: 'Roles assignment payload',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'roles',
                    description: 'Array of role IDs to assign',
                    type: 'array',
                    items: new OA\Items(type: 'integer', example: 3)
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Roles assigned successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Roles assigned successfully.'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'User already has the selected role',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'The user already has the selected role: ADMIN'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    example: [
                       'roles' => [
                           'The selected roles are invalid.'
                       ]
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error while assigning roles',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message', type: 'string', example: 'An error occurred while assigning roles.'
                ),
                new OA\Property(
                    property: 'error',
                    type: 'string',
                    example: 'SQLSTATE[23000]: Integrity constraint violation...'
                ),
            ]
        )
    )]
    public function assignRolesToUser(User $user, UserRoles $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user->load('roles');
            if (!$this->roleServices->assignRolesToUser(user: $user, data: $data)) {
                $roles = $this->roleServices->getRolesNames(user: $user, roles: $data['roles']);

                return response()->json([
                    'message' => 'The user already has the selected role: '.$roles,
                ], 400);
            }

            return response()->json([
                'message' => 'Roles assigned successfully.',
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred while assigning roles.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/admin/roles/users/{user}/delete',
        description: 'Remove roles from a user. Only accessible to Admins.',
        summary: 'Remove roles from a user',
        security: [['sanctum' => []]],
        tags: ['Roles Management'],
    )]
    #[OA\Parameter(
        name: 'user',
        description: 'User ID from whom roles will be removed',
        in: 'path',
        required: true,
        example: 12
    )]
    #[OA\RequestBody(
        description: 'Roles removal payload',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'roles',
                    description: 'Array of role IDs to remove',
                    type: 'array',
                    items: new OA\Items(type: 'integer', example: 3)
                )
            ]
        )
    )]
    #[OA\Response(
        response: 204,
        description: 'Roles removed successfully'
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    example: [
                        'roles' => [
                            'The selected roles are invalid.'
                        ]
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error while removing roles',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'An error occurred while deleting roles.'),
                new OA\Property(property: 'error', type: 'string', example: 'SQLSTATE[23000]: Integrity constraint violation...')
            ]
        )
    )]
    public function deleteRolesToUser(User $user, UserRoles $request): JsonResponse
    {
        try {
            $user->load('roles');
            $data = $request->validated();
            $this->roleServices->deleteRolesToUser(user: $user, roles: $data['roles']);

            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred while delete roles.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
