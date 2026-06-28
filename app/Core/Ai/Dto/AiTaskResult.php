<?php

namespace App\Core\Ai\Dto;

use App\Models\Ai\AiJobHistory;

final readonly class AiTaskResult
{
    public function __construct(
        public bool $success,
        public ?string $content,
        public int $responseTimeMs,
        public ?int $tokensInput = null,
        public ?int $tokensOutput = null,
        public ?int $tokensTotal = null,
        public ?string $estimatedCost = null,
        public ?string $errorMessage = null,
        public ?AiJobHistory $job = null,
    ) {}
}
