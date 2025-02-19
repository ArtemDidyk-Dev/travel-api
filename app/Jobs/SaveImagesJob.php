<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTO\StoredFileDTO;
use App\Manager\ImagesManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\ImageOptimizer\OptimizerChain;

final class SaveImagesJob implements ShouldQueue
{
    use Queueable;

    private ImagesManager $storeImagesManager;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly StoredFileDTO $storedFileDTO,
        private readonly OptimizerChain $optimizerChain,
    ) {
        $this->storeImagesManager = new ImagesManager();
    }

    public function handle(): void
    {
        $this->storeImagesManager->createIntoDatabase($this->storedFileDTO, $this->optimizerChain);
    }
}
