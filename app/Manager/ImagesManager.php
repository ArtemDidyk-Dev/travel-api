<?php

declare(strict_types=1);

namespace App\Manager;

use App\DTO\ImageDTO;
use App\DTO\StoredFileDTO;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Scopes\PublicScope;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChain;

final readonly class ImagesManager
{
    public function getModel(string $model): Builder
    {
        $modelClass = match ($model) {
            'comments' => Comment::class,
            'tours' => Tour::class,
            'images' => Image::class,
            default => throw new \InvalidArgumentException("Unsupported model type: {$model}")
        };

        return app($modelClass)->withoutGlobalScope(PublicScope::class);
    }

    public function save(ImageDTO $imageDTO, OptimizerChain $optimizerChain): string
    {
        try {
            if (! Storage::disk('public')->exists('temp_images/' . basename($imageDTO->filePath))) {
                throw new \RuntimeException('Temporary file not found');
            }
            $fileContents = Storage::disk('public')->get('temp_images/' . basename($imageDTO->filePath));
            $relativePath = $imageDTO->pathSave . '/' . $imageDTO->nameSave;
            Storage::disk('public')->put($relativePath, $fileContents);
            $absolutePath = Storage::disk('public')->path($relativePath);
            $optimizerChain->optimize($absolutePath);
            Storage::disk('public')->delete('temp_images/' . basename($imageDTO->filePath));
            return $relativePath;
        } catch (\Exception $e) {
            Log::error('Failed to save image', [
                'path' => $imageDTO->filePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function createIntoDatabase(StoredFileDTO $storedFileDTO, OptimizerChain $optimizerChain): void
    {
        $modelClass = $this->getModel($storedFileDTO->modelName);
        $model = $modelClass->find($storedFileDTO->id);
        $paths = [];
        foreach ($storedFileDTO->images as $image) {
            $path = $this->save($image, $optimizerChain);
            $paths[] = [
                'path' => $path,
            ];
        }
        $model?->images()
            ->createMany($paths);
    }

    public function updateIntoDatabase(array $storedFileDTOs, OptimizerChain $optimizerChain): void
    {
        foreach ($storedFileDTOs as $storedFileDTO) {
            $modelClass = $this->getModel($storedFileDTO->modelName);
            $model = $modelClass->find($storedFileDTO->id);
            $model?->update([
                'path' => $this->save($storedFileDTO->images, $optimizerChain),
            ]);
        }
    }
}
