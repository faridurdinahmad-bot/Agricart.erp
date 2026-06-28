<?php

namespace App\Core\Ai\Providers;

use App\Core\Ai\Dto\AiChatCompletionResult;
use App\Core\Ai\Dto\AiModelDefinition;
use App\Core\Ai\Dto\AiTestResult;
use App\Models\Ai\AiConnection;
use Illuminate\Support\Str;
use Throwable;

final class OpenRouterDriver extends AbstractAiProviderDriver
{
    public function fetchModels(AiConnection $connection): array
    {
        try {
            $response = $this->authorizeClient($connection, $this->httpClient($connection))
                ->get('/models');

            if (! $response->successful()) {
                throw new \RuntimeException(Str::limit(trim($response->json('error.message') ?? $response->body()), 500));
            }

            $models = $response->json('data') ?? [];

            return array_values(array_map(function (array $model): AiModelDefinition {
                return new AiModelDefinition(
                    id: (string) ($model['id'] ?? $model['name'] ?? ''),
                    name: (string) ($model['name'] ?? $model['id'] ?? ''),
                    contextWindow: isset($model['context_length']) ? (int) $model['context_length'] : null,
                );
            }, $models));
        } catch (Throwable $exception) {
            throw new \RuntimeException('OpenRouter model fetch failed: '.$exception->getMessage(), previous: $exception);
        }
    }

    public function testConnection(AiConnection $connection): AiTestResult
    {
        return $this->testConnectionUsingPrompt($connection);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function completeChat(
        AiConnection $connection,
        array $messages,
        ?int $maxTokens = null,
        bool $jsonResponse = false,
    ): AiChatCompletionResult {
        return $this->openAiCompatibleChat($connection, $messages, $maxTokens, [
            'HTTP-Referer' => config('app.url', 'https://agricart.test'),
            'X-Title' => config('app.name', 'Agricart ERP'),
        ], $jsonResponse);
    }
}
