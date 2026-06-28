<?php

namespace App\Modules\Catalog\Support;

use App\Core\Ai\Dto\AiFieldDefinition;

final class CategoryAiContentSchema
{
    /**
     * @return list<AiFieldDefinition>
     */
    public static function fields(): array
    {
        return [
            new AiFieldDefinition('name_ur', maxLength: 255),
            new AiFieldDefinition('short_description_en'),
            new AiFieldDefinition('short_description_ur'),
            new AiFieldDefinition('long_description_en'),
            new AiFieldDefinition('long_description_ur'),
            new AiFieldDefinition('usage_en'),
            new AiFieldDefinition('usage_ur'),
            new AiFieldDefinition('benefits_en'),
            new AiFieldDefinition('benefits_ur'),
            new AiFieldDefinition('warnings_en', required: false),
            new AiFieldDefinition('warnings_ur', required: false),
            new AiFieldDefinition('seo_title', maxLength: 255),
            new AiFieldDefinition('seo_focus_keyword_en', maxLength: 255),
            new AiFieldDefinition('seo_focus_keyword_ur', maxLength: 255),
            new AiFieldDefinition('meta_description'),
            new AiFieldDefinition('meta_keywords', required: false, maxLength: 500),
            new AiFieldDefinition('og_title', required: false, maxLength: 255),
            new AiFieldDefinition('og_description', required: false),
            new AiFieldDefinition('synonyms_en', required: false, maxLength: 500),
            new AiFieldDefinition('synonyms_ur', required: false, maxLength: 500),
            new AiFieldDefinition('alternate_spellings', required: false, maxLength: 500),
            new AiFieldDefinition('search_aliases', required: false, maxLength: 500),
        ];
    }

    /**
     * @return list<string>
     */
    public static function jsonKeys(): array
    {
        return array_map(
            fn (AiFieldDefinition $field): string => $field->key,
            self::fields(),
        );
    }

    /**
     * Maps parsed JSON keys to category form field names.
     *
     * @return array<string, string>
     */
    public static function formFieldMap(): array
    {
        $map = [
            'name_ur' => 'urdu_name',
        ];

        foreach (self::jsonKeys() as $key) {
            if ($key === 'name_ur') {
                continue;
            }

            $map[$key] = $key;
        }

        return $map;
    }

    /**
     * Maps category form field names to database column names.
     */
    public static function databaseColumnForFormField(string $formField): ?string
    {
        if ($formField === 'urdu_name') {
            return 'name_ur';
        }

        if (in_array($formField, self::formFieldKeys(), true)) {
            return $formField;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function formFieldKeys(): array
    {
        return array_values(self::formFieldMap());
    }
}
