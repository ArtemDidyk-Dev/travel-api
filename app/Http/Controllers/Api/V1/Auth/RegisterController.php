<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enum\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class RegisterController extends Controller
{
    #[OA\Post(
        path: '/api/v1/auth/register',
        description: 'Registers a new user and returns a token on success.',
        summary: 'User registration',
        requestBody: new OA\RequestBody(
            description: 'User registration data',
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret12345'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully, returns a token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'your_generated_token_here'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation errors',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Validation errors'
                        ),
                        new OA\Property(
                            property: 'errors',
                            properties: [
                                new OA\Property(
                                    property: 'email',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ["The email has already been taken."]
                                ),
                                new OA\Property(
                                    property: 'password',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ["The password field is required."]
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Registration failed. Please try again later.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Registration failed. Please try again later.'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(UserRegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = User::create($data);
            $token = $user->createToken("Token of user: {$user->name}")
                ->plainTextToken;
            $role = Role::where('name', RoleEnum::USER->name)->first();
            $user->roles()
                ->attach($role);

            return response()->json([
                'token' => $token,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration failed. Please try again later.',
            ], 500);
        }
    }
}
