<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRoles;
use App\Http\Resources\RoleResource;
use App\Models\Role;
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

    public function assignRolesToUser(UserRoles $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if (! $this->roleServices->assignRolesToUser($data)) {
                $roles = $this->roleServices->getRolesNames($data['roles']);
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

    public function deleteRolesToUser(UserRoles $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->roleServices->deleteRolesToUser($data);
            return response()->json([], 204);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred while delete roles.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
