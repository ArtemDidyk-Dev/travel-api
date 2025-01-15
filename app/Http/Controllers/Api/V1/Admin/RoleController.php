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

class RoleController extends Controller
{
    public function __construct(
        readonly private RoleServicesInterface $roleServices
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection(Role::all());
    }

    public function assignRolesToUser(User $user, UserRoles $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user->load('roles');
            if (! $this->roleServices->assignRolesToUser(user: $user, data: $data)) {
                $roles = $this->roleServices->getRolesNames(user: $user, roles: $data['roles']);

                return response()->json([
                    'message' => 'The user already has the selected role: ' . $roles,
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
