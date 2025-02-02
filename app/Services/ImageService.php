<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\ImagePath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Pngquant;

final readonly class ImageService implements ImageInterface
{
    private OptimizerChain $optimizerChain;

    public function __construct()
    {

        $this->optimizerChain = (new OptimizerChain())
            ->addOptimizer(new Jpegoptim(['--strip-all', '--all-progressive', '-m60']))
            ->addOptimizer(new Pngquant(['--force']))

            ->setTimeout(120);
    }

    public function save(Model $model, array $files, ImagePath $path): void
    {

        foreach ($files as $file) {
            $name = $this->generateUniqueFileName($file);
            $filePath = $this->storeFile($file, $path->value, $name);
            $model->images()
                ->create([
                    'path' => $filePath,
                ]);
        }
    }

    public function delete(Model $model, array $fileIds): void
    {
        $imagesToDelete = $model->images->whereIn('id', $fileIds);
        $pathsToDelete = $imagesToDelete->pluck('path')
            ->all();
        Storage::disk('public')->delete($pathsToDelete);
        $imagesToDelete->each->delete();
    }

    public function update(Model $model, array $files, ImagePath $path): void
    {
        $imagesToUpdate = $model->images->whereIn('id', array_keys($files));
        $this->deleteOldFiles($imagesToUpdate);
        $this->updateImages($imagesToUpdate, $files, $path);
    }

    public function processFile(UploadedFile $file, ImagePath $path): string
    {
        $name = $this->generateUniqueFileName($file);

        return $this->storeFile($file, $path->value, $name);
    }

    private function generateUniqueFileName($file): string
    {
        $extension = $file->getClientOriginalExtension();

        return uniqid('', true) . '.' . $extension;
    }

    private function storeFile($file, string $path, string $name): string
    {
        $setFilePath = Storage::disk('public')->putFileAs($path, $file, $name);
        $filePath = storage_path('app/public/' . $setFilePath);
        $this->optimizerChain->optimize($filePath);
        return $setFilePath;
    }

    private function deleteOldFiles(Collection $images): void
    {
        $pathsToDelete = $images->pluck('path')
            ->all();
        Storage::disk('public')->delete($pathsToDelete);
    }

    private function updateImages(Collection $images, array $files, ImagePath $path): void
    {
        $images->each(function (Model $image) use ($files, $path) {
            if (isset($files[$image->id])) {
                $file = $files[$image->id];
                $filePath = $this->processFile($file, $path);
                $image->update([
                    'path' => $filePath,
                ]);
            }
        });

    }
}
