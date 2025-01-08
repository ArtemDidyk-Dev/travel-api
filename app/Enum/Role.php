<?php

declare(strict_types=1);

namespace App\Enum;

enum Role: int
{
    case ADMIN = 1;
    case EDITOR = 2;
    case USER = 3;
}
