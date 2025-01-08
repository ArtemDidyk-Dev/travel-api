<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enum\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(UserRegisterRequest $request): JsonResponse
    {
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
    }
}
