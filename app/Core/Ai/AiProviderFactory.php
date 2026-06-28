<?php

namespace App\Core\Ai;

use App\Core\Ai\Contracts\AiProviderDriver;
use App\Core\Ai\Enums\AiProvider;
use App\Core\Ai\Providers\AnthropicDriver;
use App\Core\Ai\Providers\AzureOpenAiDriver;
use App\Core\Ai\Providers\GeminiDriver;
use App\Core\Ai\Providers\OllamaDriver;
use App\Core\Ai\Providers\OpenAiDriver;
use App\Core\Ai\Providers\OpenRouterDriver;
use InvalidArgumentException;

final class AiProviderFactory
{
    public static function make(AiProvider $provider): AiProviderDriver
    {
        return match ($provider) {
            AiProvider::OpenRouter => new OpenRouterDriver,
            AiProvider::OpenAi => new OpenAiDriver,
            AiProvider::Gemini => new GeminiDriver,
            AiProvider::Anthropic => new AnthropicDriver,
            AiProvider::AzureOpenAi => new AzureOpenAiDriver,
            AiProvider::Ollama => new OllamaDriver,
        };
    }

    public static function makeFromString(string $provider): AiProviderDriver
    {
        $enum = AiProvider::tryFrom($provider);

        if (! $enum) {
            throw new InvalidArgumentException("Unknown AI provider [{$provider}].");
        }

        return self::make($enum);
    }
}
