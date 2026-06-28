<?php

namespace App\Core\Ai\Enums;

enum AiPromptOutputFormat: string
{
    case Json = 'json';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Json => 'JSON',
            self::Text => 'Text',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $format): array => [
                'value' => $format->value,
                'label' => $format->label(),
            ],
            self::cases(),
        );
    }
}
