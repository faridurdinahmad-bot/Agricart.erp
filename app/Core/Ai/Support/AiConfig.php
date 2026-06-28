<?php

namespace App\Core\Ai\Support;

final class AiConfig
{
    /**
     * @return array<string, int|float>
     */
    public static function connectionDefaults(): array
    {
        return config('ai.defaults', [
            'context_window' => 128000,
            'max_output_tokens' => 4096,
            'temperature' => 0.7,
            'timeout' => 60,
            'retry_count' => 2,
            'connection_test_max_tokens' => 16,
        ]);
    }

    public static function connectionTestMaxTokens(): int
    {
        return (int) (self::connectionDefaults()['connection_test_max_tokens'] ?? 16);
    }

    public static function maxStoredModels(): int
    {
        return (int) config('ai.models.max_stored', 500);
    }

    public static function customPromptMaxLength(): int
    {
        return (int) config('ai.tasks.custom_prompt_max_length', 8000);
    }

    public static function promptSystemMaxLength(): int
    {
        return (int) config('ai.prompts.system_max_length', 12000);
    }

    public static function promptUserMaxLength(): int
    {
        return (int) config('ai.prompts.user_max_length', 24000);
    }
}
