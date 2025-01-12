<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
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
