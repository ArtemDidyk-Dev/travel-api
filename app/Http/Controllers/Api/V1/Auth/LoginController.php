<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    #[OA\Post(
        path: '/api/auth/login',
        description: 'Authenticates a user and returns an access token. (But if the user is login, his redirect to travels)',
        summary: 'User login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful login',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'token', type: 'string', example: '1|abcdefg1234567')]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Wrong email or password'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation errors',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                        new OA\Property(
                            property: 'errors',
                            properties: [
                                new OA\Property(
                                    property: 'email',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['The selected email is invalid.']
                                ),
                                new OA\Property(
                                    property: 'password',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['The password field is required.']
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Login failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Login failed. Please try again later.'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(UserLoginRequest $request): JsonResponse
    {
        try {
            if (! Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'message' => 'Wrong email or password',
                ], 401);
            }
            $user = User::query()->where('email', $request->email)->first();
            $user->tokens()
                ->delete();
            return response()->json([
                'token' => $user->createToken("Token of user: {$user->name}")
                    ->plainTextToken,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Login failed. Please try again later.',
            ], 500);
        }
    }
}
