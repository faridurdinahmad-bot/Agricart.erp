<?php

namespace App\Core\Ai\Dto;

final readonly class AiFieldDefinition
{
    public function __construct(
        public string $key,
        public bool $required = true,
        public ?int $maxLength = null,
    ) {}
}
