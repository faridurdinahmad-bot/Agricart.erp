<?php

namespace App\Core\Ai\Dto;

final readonly class AiChatCompletionResult
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function __construct(
        public bool $success,
        public ?string $content,
        public int $responseTimeMs,
        public ?int $tokensInput = null,
        public ?int $tokensOutput = null,
        public ?int $tokensTotal = null,
        public ?string $estimatedCost = null,
        public ?string $errorMessage = null,
        public array $messages = [],
    ) {}
}
