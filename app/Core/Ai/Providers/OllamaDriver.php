<?php

namespace App\Core\Ai\Providers;

use App\Core\Ai\Dto\AiChatCompletionResult;
use App\Core\Ai\Dto\AiModelDefinition;
use App\Core\Ai\Dto\AiTestResult;
use App\Models\Ai\AiConnection;
use Illuminate\Support\Str;
use Throwable;

final class OllamaDriver extends AbstractAiProviderDriver
{
    public function fetchModels(AiConnection $connection): array
    {
        $client = $this->httpClient($connection);

        if (filled($connection->api_key)) {
            $client = $client->withToken($connection->api_key);
        }

        $response = $client->get('/api/tags');

        if (! $response->successful()) {
            $response->throw();
        }

        $models = $response->json('models') ?? [];

        return array_values(array_map(function (array $model): AiModelDefinition {
            return new AiModelDefinition(
                id: (string) ($model['name'] ?? $model['model'] ?? ''),
                name: (string) ($model['name'] ?? $model['model'] ?? ''),
            );
        }, $models));
    }

    public function testConnection(AiConnection $connection): AiTestResult
    {
        $completion = $this->completeChat($connection, [
            ['role' => 'user', 'content' => 'Reply with exactly: OK'],
        ], 16);

        if (! $completion->success) {
            return new AiTestResult(
                success: false,
                provider: $connection->provider,
                model: $connection->model,
                responseTimeMs: $completion->responseTimeMs,
                errorMessage: $completion->errorMessage,
            );
        }

        return new AiTestResult(
            success: true,
            provider: $connection->provider,
            model: $connection->model,
            responseTimeMs: $completion->responseTimeMs,
        );
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
        $started = microtime(true);

        try {
            $client = $this->httpClient($connection);

            if (filled($connection->api_key)) {
                $client = $client->withToken($connection->api_key);
            }

            $response = $client->post('/api/chat', [
                'model' => $connection->model,
                'messages' => $messages,
                'stream' => false,
                'options' => [
                    'num_predict' => $maxTokens ?? min(512, $connection->max_output_tokens),
                ],
            ]);

            $elapsed = (int) round((microtime(true) - $started) * 1000);

            if (! $response->successful()) {
                return new AiChatCompletionResult(
                    success: false,
                    content: null,
                    responseTimeMs: $elapsed,
                    errorMessage: Str::limit(trim($response->json('error') ?? $response->body()), 2000),
                    messages: $messages,
                );
            }

            $content = trim((string) data_get($response->json(), 'message.content', ''));

            if ($content === '') {
                return new AiChatCompletionResult(
                    success: false,
                    content: null,
                    responseTimeMs: $elapsed,
                    errorMessage: 'Ollama returned an empty response.',
                    messages: $messages,
                );
            }

            $input = data_get($response->json(), 'prompt_eval_count');
            $output = data_get($response->json(), 'eval_count');

            return new AiChatCompletionResult(
                success: true,
                content: $content,
                responseTimeMs: $elapsed,
                tokensInput: is_numeric($input) ? (int) $input : null,
                tokensOutput: is_numeric($output) ? (int) $output : null,
                tokensTotal: is_numeric($input) && is_numeric($output) ? ((int) $input + (int) $output) : null,
                messages: $messages,
            );
        } catch (Throwable $exception) {
            $elapsed = (int) round((microtime(true) - $started) * 1000);

            return new AiChatCompletionResult(
                success: false,
                content: null,
                responseTimeMs: $elapsed,
                errorMessage: Str::limit($exception->getMessage(), 2000),
                messages: $messages,
            );
        }
    }
}
