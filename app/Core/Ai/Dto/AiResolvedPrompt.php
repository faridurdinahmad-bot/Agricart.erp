<?php

namespace App\Core\Ai\Dto;

final readonly class AiResolvedPrompt
{
    public function __construct(
        public string $system,
        public string $user,
        public ?float $temperature = null,
        public ?int $maxOutputTokens = null,
        public bool $jsonResponse = false,
    ) {}

    /**
     * @return array{system: string, user: string}
     */
    public function messages(): array
    {
        return [
            'system' => $this->system,
            'user' => $this->user,
        ];
    }
}
