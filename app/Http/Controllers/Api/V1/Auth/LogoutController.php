<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
class LogoutController extends Controller
{
    #[OA\Post(
        path: "/api/v1/auth/logout",
        description: "This endpoint logs out the authenticated user by revoking all issued tokens.",
        summary: "Logout user and revoke tokens",
        security: [["sanctum" => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 204,
                description: "Tokens successfully revoked. No content returned."
            ),
            new OA\Response(
                response: 500,
                description: "Unable to revoke tokens. Please try again later.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Unable to revoke tokens. Please try again later.")
                    ]
                )
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $request->user()
                ->tokens()
                ->delete();
            return response()->json([], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to revoke tokens. Please try again later.',
            ], 500);
        }
    }
}
