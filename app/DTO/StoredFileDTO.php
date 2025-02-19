<?php

declare(strict_types=1);

namespace App\DTO;

final class StoredFileDTO
{
    public string $modelName;

    public int $id;

    public array|ImageDTO $images;
}
