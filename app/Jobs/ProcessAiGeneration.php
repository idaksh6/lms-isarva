<?php

namespace App\Jobs;

use App\Models\AiGeneration;
use App\Services\Ai\AiGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAiGeneration implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public int $aiGenerationId) {}

    public function handle(AiGenerationService $service): void
    {
        $generation = AiGeneration::query()->find($this->aiGenerationId);

        if (! $generation || ! $generation->isPending()) {
            return;
        }

        $service->run($generation);
    }
}
