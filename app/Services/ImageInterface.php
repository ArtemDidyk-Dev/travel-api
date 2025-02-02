<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\ImagePath;
use Illuminate\Database\Eloquent\Model;

interface ImageInterface
{
    public function save(Model $model, array $files, ImagePath $path);

    public function delete(Model $model, array $fileIds);

    public function update(Model $model, array $files, ImagePath $path);
}
