<?php

namespace App\Core\Ai\Dto;

use App\Core\Ai\Enums\AiConnectionTestStatus;
use App\Core\Ai\Enums\AiProvider;

final readonly class AiTestResult
{
    public function __construct(
        public bool $success,
        public AiProvider $provider,
        public string $model,
        public int $responseTimeMs,
        public ?string $rateLimitRemaining = null,
        public ?string $errorMessage = null,
    ) {}

    public function status(): AiConnectionTestStatus
    {
        return $this->success
            ? AiConnectionTestStatus::Connected
            : AiConnectionTestStatus::Disconnected;
    }

    public function headline(): string
    {
        return $this->success
            ? 'Connected Successfully ✅'
            : 'Connection Failed ❌';
    }

    /**
     * @return array<string, string|null>
     */
    public function details(): array
    {
        return [
            'Provider' => $this->provider->label(),
            'Selected Model' => $this->model,
            'Response Time' => $this->responseTimeMs.' ms',
            'Remaining Rate Limit' => $this->rateLimitRemaining ?? 'Not reported by provider',
            'Error' => $this->errorMessage,
        ];
    }
}
