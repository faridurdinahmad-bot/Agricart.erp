<?php

namespace App\Modules\Catalog\Support;

use App\Core\Ai\Dto\AiFieldDefinition;

final class BrandAiContentSchema
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
            new AiFieldDefinition('brand_overview_en'),
            new AiFieldDefinition('brand_overview_ur'),
            new AiFieldDefinition('seo_title', maxLength: 255),
            new AiFieldDefinition('seo_description'),
            new AiFieldDefinition('seo_keywords', required: false, maxLength: 500),
            new AiFieldDefinition('country', required: false, maxLength: 100),
            new AiFieldDefinition('website', required: false, maxLength: 500),
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

    /**
     * Fields AI should only fill when confident — empty string means skip.
     *
     * @return list<string>
     */
    public static function uncertainOptionalKeys(): array
    {
        return ['country', 'website'];
    }
}
