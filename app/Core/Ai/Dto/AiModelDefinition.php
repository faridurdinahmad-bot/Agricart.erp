<?php

namespace App\Core\Ai\Dto;

final readonly class AiModelDefinition
{
    public function __construct(
        public string $id,
        public string $name,
        public ?int $contextWindow = null,
        public ?int $maxOutputTokens = null,
    ) {}

    /**
     * @return array{id: string, name: string, context_window: int|null, max_output_tokens: int|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'context_window' => $this->contextWindow,
            'max_output_tokens' => $this->maxOutputTokens,
        ];
    }

    /**
     * @param  array{id: string, name: string, context_window?: int|null, max_output_tokens?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            contextWindow: $data['context_window'] ?? null,
            maxOutputTokens: $data['max_output_tokens'] ?? null,
        );
    }
}
