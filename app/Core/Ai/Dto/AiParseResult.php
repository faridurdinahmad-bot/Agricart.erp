<?php

namespace App\Core\Ai\Dto;

final readonly class AiParseResult
{
    /**
     * @param  array<string, string>  $data
     * @param  list<string>  $errors
     */
    public function __construct(
        public bool $success,
        public array $data = [],
        public array $errors = [],
    ) {}

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    public function errorSummary(): string
    {
        if ($this->errors === []) {
            return 'The AI response could not be parsed.';
        }

        return implode(' ', $this->errors);
    }
}
