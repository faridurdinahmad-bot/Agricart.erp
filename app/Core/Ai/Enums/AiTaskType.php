<?php

namespace App\Core\Ai\Enums;

enum AiTaskType: string
{
    case Translate = 'translate';
    case CategoryContent = 'category_content';
    case ProductContent = 'product_content';
    case BrandContent = 'brand_content';
    case AttributeContent = 'attribute_content';
    case SeoGeneration = 'seo_generation';
    case Summarize = 'summarize';
    case CustomPrompt = 'custom_prompt';
    case ConnectionTest = 'connection_test';

    public function label(): string
    {
        return match ($this) {
            self::Translate => 'Translate',
            self::CategoryContent => 'Category Content',
            self::ProductContent => 'Product Content',
            self::BrandContent => 'Brand Content',
            self::AttributeContent => 'Attribute Content',
            self::SeoGeneration => 'SEO Generation',
            self::Summarize => 'Summarize',
            self::CustomPrompt => 'Custom Prompt',
            self::ConnectionTest => 'Connection Test',
        };
    }

    public function isModuleTask(): bool
    {
        return $this !== self::ConnectionTest;
    }

    public function requiresJsonResponse(): bool
    {
        return in_array($this, [
            self::CategoryContent,
            self::ProductContent,
            self::BrandContent,
            self::AttributeContent,
            self::SeoGeneration,
        ], true);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function moduleTaskOptions(): array
    {
        return array_map(
            fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            array_filter(self::cases(), fn (self $type): bool => $type->isModuleTask()),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases(),
        );
    }
}
