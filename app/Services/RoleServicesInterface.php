<?php

declare(strict_types=1);

namespace App\Services;

interface RoleServicesInterface
{
    public function assignRolesToUser(array $data): bool;

    public function getRolesNames(array $roles): string;

    public function deleteRolesToUser(array $data): void;
}
