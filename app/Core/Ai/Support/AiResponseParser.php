<?php

namespace App\Core\Ai\Support;

use App\Core\Ai\Dto\AiFieldDefinition;
use App\Core\Ai\Dto\AiParseResult;

/**
 * Generic JSON response parser for all Agricart AI modules.
 */
final class AiResponseParser
{
    /**
     * @param  list<AiFieldDefinition>  $fields
     */
    public function parse(string $rawContent, array $fields): AiParseResult
    {
        $jsonString = self::extractJsonString($rawContent);

        if ($jsonString === null) {
            return new AiParseResult(
                success: false,
                errors: ['AI did not return valid JSON.'],
            );
        }

        try {
            $decoded = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new AiParseResult(
                success: false,
                errors: ['AI returned malformed JSON.'],
            );
        }

        if (! is_array($decoded)) {
            return new AiParseResult(
                success: false,
                errors: ['AI JSON must be an object with field keys.'],
            );
        }

        $errors = [];
        $data = [];

        foreach ($fields as $field) {
            $value = $decoded[$field->key] ?? null;

            if ($value === null || (is_string($value) && trim($value) === '')) {
                if ($field->required) {
                    $errors[] = "Missing required field [{$field->key}].";
                }

                continue;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                $errors[] = "Field [{$field->key}] must be a string.";

                continue;
            }

            $stringValue = trim((string) $value);

            if ($field->maxLength !== null && mb_strlen($stringValue) > $field->maxLength) {
                $errors[] = "Field [{$field->key}] exceeds maximum length of {$field->maxLength} characters.";

                continue;
            }

            $data[$field->key] = $stringValue;
        }

        if ($errors !== []) {
            return new AiParseResult(success: false, data: $data, errors: $errors);
        }

        return new AiParseResult(success: true, data: $data);
    }

    public static function extractJsonString(string $content): ?string
    {
        $content = trim($content);

        if ($content === '') {
            return null;
        }

        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $content, $matches)) {
            $content = trim($matches[1]);
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return trim(substr($content, $start, $end - $start + 1));
    }
}
