<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ImageDTO;
use App\DTO\StoredFileDTO;
use App\Enum\ImagePath;
use App\Jobs\SaveImagesJob;
use App\Jobs\UpdateImagesJob;
use App\Manager\ImagesManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Pngquant;

final class ImageService implements ImageInterface
{
    private OptimizerChain $optimizerChain;

    private ImagesManager $storeImagesManager;

    public function __construct()
    {

        $this->optimizerChain = (new OptimizerChain())
            ->addOptimizer(new Jpegoptim(['--strip-all', '--all-progressive', '-m60']))
            ->addOptimizer(new Pngquant(['--force']))

            ->setTimeout(120);
        $this->storeImagesManager = new ImagesManager();
    }

    public function save(Model $model, array $files, ImagePath $path, bool $async = false): void
    {
        $storedFileDto = new StoredFileDto();
        $storedFileDto->modelName = $model->getTable();
        $storedFileDto->id = $model->id;
        $images = [];
        foreach ($files as $file) {
            $image = new ImageDTO(
                pathSave: $path->value,
                nameSave: $this->generateUniqueFileName($file),
                filePath: $file->store('temp_images', 'public'),
                fileName: $file->getClientOriginalName(),
            );
            $images[] = $image;
        }
        $storedFileDto->images = $images;

        if ($async) {
            SaveImagesJob::dispatch($storedFileDto, $this->optimizerChain);
            return;
        }
        $this->storeImagesManager->createIntoDatabase($storedFileDto, $this->optimizerChain);
    }

    public function delete(Model $model, array $fileIds): void
    {
        $imagesToDelete = $model->images->whereIn('id', $fileIds);
        $pathsToDelete = $imagesToDelete->pluck('path')
            ->all();
        Storage::disk('public')->delete($pathsToDelete);
        $imagesToDelete->each->delete();
    }

    public function update(Model $model, array $files, ImagePath $path, $async = false): void
    {
        $imagesToUpdate = $model->images->whereIn('id', array_keys($files));
        $this->deleteOldFiles($imagesToUpdate);
        $this->updateImages($imagesToUpdate, $files, $path, $async);
    }

    private function generateUniqueFileName($file): string
    {
        $extension = $file->getClientOriginalExtension();

        return uniqid('', true) . '.' . $extension;
    }

    private function deleteOldFiles(Collection $images): void
    {
        $pathsToDelete = $images->pluck('path')
            ->all();
        Storage::disk('public')->delete($pathsToDelete);
    }

    private function updateImages(Collection $images, array $files, ImagePath $path, bool $async = false): void
    {
        $storedFilesDTOs = [];
        $images->each(function (Model $image) use ($files, $path, &$storedFilesDTOs) {
            if (isset($files[$image->id])) {
                $storedFilesDTO = new StoredFileDTO();
                $storedFilesDTO->id = $image->id;
                $storedFilesDTO->modelName = $image->getTable();
                $file = $files[$image->id];

                $imageDTO = new ImageDTO(
                    pathSave: $path->value,
                    nameSave: $this->generateUniqueFileName($file),
                    filePath: $file->store('temp_images', 'public'),
                    fileName: $file->getClientOriginalName(),
                );
                $storedFilesDTO->images = $imageDTO;
                $storedFilesDTOs[] = $storedFilesDTO;
            }
        });

        if ($async) {
            UpdateImagesJob::dispatch($storedFilesDTOs, $this->optimizerChain);
            return;
        }
        $this->storeImagesManager->updateIntoDatabase($storedFilesDTOs, $this->optimizerChain);
    }
}
