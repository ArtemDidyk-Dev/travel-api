<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class RoleServices implements RoleServicesInterface
{
    public function assignRolesToUser(User $user, array $data): bool
    {
        if ($user->roles()->whereIn('role_id', $data['roles'])->exists()) {
            return false;
        }
        $user->roles()
            ->attach($data['roles']);

        return true;
    }

    public function getRolesNames(User $user, array $roles): string
    {
        return $user->roles()
            ->whereIn('role_id', $roles)
            ->pluck('name')
            ->implode(', ');
    }

    public function deleteRolesToUser(User $user, array $roles): void
    {
        $user->roles()
            ->detach($roles);
    }
}
