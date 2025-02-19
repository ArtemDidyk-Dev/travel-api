<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Manager\ImagesManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\ImageOptimizer\OptimizerChain;

final class UpdateImagesJob implements ShouldQueue
{
    use Queueable;

    private ImagesManager $updateImagesManager;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $storedFileDTOs,
        private readonly OptimizerChain $optimizerChain,
    ) {
        $this->updateImagesManager = new ImagesManager();
    }

    public function handle(): void
    {
        $this->updateImagesManager->updateIntoDatabase($this->storedFileDTOs, $this->optimizerChain);
    }
}
