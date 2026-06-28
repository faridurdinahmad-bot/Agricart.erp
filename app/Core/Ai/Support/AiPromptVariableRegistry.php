<?php

namespace App\Core\Ai\Support;

use App\Core\Ai\Enums\AiTaskType;
use App\Modules\Catalog\Support\CategoryAiContentSchema;

final class AiPromptVariableRegistry
{
    /**
     * @return list<string>
     */
    public static function forTaskType(AiTaskType $taskType): array
    {
        return match ($taskType) {
            AiTaskType::CategoryContent => [
                '{{english_name}}',
                '{{urdu_name}}',
                '{{hs_code}}',
                '{{category}}',
                '{{image}}',
                '{{required_keys}}',
                '{{optional_keys}}',
                '{{ai_prompt_override}}',
            ],
            AiTaskType::ProductContent => [
                '{{english_name}}',
                '{{urdu_name}}',
                '{{product}}',
                '{{category}}',
                '{{brand}}',
                '{{attributes}}',
                '{{image}}',
            ],
            AiTaskType::BrandContent => [
                '{{english_name}}',
                '{{urdu_name}}',
                '{{brand}}',
                '{{image}}',
            ],
            AiTaskType::AttributeContent => [
                '{{english_name}}',
                '{{urdu_name}}',
                '{{attributes}}',
                '{{category}}',
            ],
            AiTaskType::Translate => [
                '{{source_text}}',
                '{{source_language}}',
                '{{target_language}}',
            ],
            AiTaskType::SeoGeneration => [
                '{{subject}}',
                '{{language}}',
                '{{english_name}}',
                '{{category}}',
                '{{brand}}',
            ],
            AiTaskType::CustomPrompt => [
                '{{prompt}}',
                '{{english_name}}',
                '{{category}}',
                '{{brand}}',
                '{{attributes}}',
            ],
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    public static function commonVariables(): array
    {
        return [
            '{{english_name}}',
            '{{urdu_name}}',
            '{{image}}',
            '{{category}}',
            '{{brand}}',
            '{{attributes}}',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string>
     */
    public static function enrichContext(AiTaskType $taskType, array $context): array
    {
        $values = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $values[(string) $key] = trim((string) ($value ?? ''));
            }
        }

        $aliases = [
            'english_name' => ['name_en', 'english_name'],
            'urdu_name' => ['name_ur', 'urdu_name'],
            'category' => ['category', 'category_name', 'name_en'],
            'brand' => ['brand', 'brand_name'],
            'product' => ['product', 'product_name', 'name_en'],
            'attributes' => ['attributes', 'attribute_list'],
            'image' => ['image', 'image_name', 'image_filename'],
        ];

        foreach ($aliases as $canonical => $keys) {
            if (($values[$canonical] ?? '') !== '') {
                continue;
            }

            foreach ($keys as $key) {
                if (($values[$key] ?? '') !== '') {
                    $values[$canonical] = $values[$key];
                    break;
                }
            }
        }

        if ($taskType === AiTaskType::CategoryContent) {
            $values['required_keys'] ??= implode(', ', array_map(
                fn ($field) => $field->key,
                array_filter(
                    CategoryAiContentSchema::fields(),
                    fn ($field) => $field->required,
                ),
            ));

            $values['optional_keys'] ??= implode(', ', array_map(
                fn ($field) => $field->key,
                array_filter(
                    CategoryAiContentSchema::fields(),
                    fn ($field) => ! $field->required,
                ),
            ));
        }

        return $values;
    }
}
