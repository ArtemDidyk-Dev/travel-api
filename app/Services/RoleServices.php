<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;

final readonly class RoleServices implements RoleServicesInterface
{
    public function assignRolesToUser(array $data): bool
    {
        $user = User::with('roles')->find($data['user']);
        if ($user?->roles()->whereIn('role_id', $data['roles'])->exists()) {
            return false;
        }
        $user?->roles()
            ->attach($data['roles']);

        return true;
    }

    public function getRolesNames(array $roles): string
    {
        return Role::whereIn('id', $roles)->pluck('name')->implode(', ');
    }

    public function deleteRolesToUser(array $data): void
    {
        $user = User::with('roles')->find($data['user']);
        $user?->roles()
            ->detach($data['roles']);
    }
}
