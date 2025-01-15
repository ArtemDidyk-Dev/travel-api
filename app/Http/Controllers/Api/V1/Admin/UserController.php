<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $users = User::with(['roles'])->paginate();
        return UserResource::collection($users);
    }

    public function show(User $user): UserResource
    {
        $user->load('roles');
        return UserResource::make($user);
    }

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
