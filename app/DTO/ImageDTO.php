<?php

declare(strict_types=1);

namespace App\DTO;

final class ImageDTO
{
    public function __construct(
        public string $pathSave,
        public string $nameSave,
        public string $filePath,
        public string $fileName,
    ) {
    }
}
