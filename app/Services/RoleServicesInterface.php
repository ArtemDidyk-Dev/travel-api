<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

interface RoleServicesInterface
{
    public function assignRolesToUser(User $user, array $data): bool;

    public function getRolesNames(User $user, array $roles): string;

    public function deleteRolesToUser(User $user, array $roles): void;
}
