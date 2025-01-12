<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
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
