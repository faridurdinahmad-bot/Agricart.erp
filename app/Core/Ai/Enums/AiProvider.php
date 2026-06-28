<?php

namespace App\Core\Ai\Enums;

enum AiProvider: string
{
    case OpenRouter = 'openrouter';
    case OpenAi = 'openai';
    case Gemini = 'gemini';
    case Anthropic = 'anthropic';
    case AzureOpenAi = 'azure_openai';
    case Ollama = 'ollama';

    public function isImplemented(): bool
    {
        return in_array($this, [self::OpenRouter, self::OpenAi, self::Ollama], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::OpenRouter => 'OpenRouter',
            self::OpenAi => 'OpenAI',
            self::Gemini => 'Google Gemini',
            self::Anthropic => 'Anthropic',
            self::AzureOpenAi => 'Azure OpenAI',
            self::Ollama => 'Local Ollama',
        };
    }

    public function defaultBaseUrl(): string
    {
        return match ($this) {
            self::OpenRouter => 'https://openrouter.ai/api/v1',
            self::OpenAi => 'https://api.openai.com/v1',
            self::Gemini => 'https://generativelanguage.googleapis.com/v1beta',
            self::Anthropic => 'https://api.anthropic.com/v1',
            self::AzureOpenAi => '',
            self::Ollama => 'http://127.0.0.1:11434',
        };
    }

    /**
     * Providers with working drivers in this release.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function settingsOptions(): array
    {
        return array_map(
            fn (self $provider): array => [
                'value' => $provider->value,
                'label' => $provider->label(),
            ],
            array_filter(self::cases(), fn (self $provider): bool => $provider->isImplemented()),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $provider): array => [
                'value' => $provider->value,
                'label' => $provider->label(),
            ],
            self::cases(),
        );
    }
}
