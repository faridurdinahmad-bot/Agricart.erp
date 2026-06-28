<?php

namespace App\Core\Ai\Dto;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;

final readonly class AiTaskRequest
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public AiTaskType $taskType,
        public AiTargetModule $targetModule,
        public array $context = [],
        public ?string $targetType = null,
        public ?int $targetId = null,
        public ?string $customPrompt = null,
        public ?int $connectionId = null,
        public ?int $userId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public static function make(
        AiTaskType $taskType,
        AiTargetModule $targetModule,
        array $context = [],
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $customPrompt = null,
        ?int $connectionId = null,
        ?int $userId = null,
    ): self {
        return new self(
            taskType: $taskType,
            targetModule: $targetModule,
            context: $context,
            targetType: $targetType,
            targetId: $targetId,
            customPrompt: $customPrompt,
            connectionId: $connectionId,
            userId: $userId ?? auth()->id(),
        );
    }
}
