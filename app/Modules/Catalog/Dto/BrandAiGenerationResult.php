<?php

namespace App\Modules\Catalog\Dto;

final readonly class BrandAiGenerationResult
{
    /**
     * @param  array<string, string>  $formFields
     */
    public function __construct(
        public bool $success,
        public string $message,
        public array $formFields = [],
        public ?string $generatedLabel = null,
        public ?string $model = null,
    ) {}

    public static function failed(string $message): self
    {
        return new self(success: false, message: $message);
    }

    /**
     * @param  array<string, string>  $formFields
     */
    public static function succeeded(array $formFields, string $model, string $generatedLabel): self
    {
        return new self(
            success: true,
            message: 'AI content generated successfully.',
            formFields: $formFields,
            generatedLabel: $generatedLabel,
            model: $model,
        );
    }
}
