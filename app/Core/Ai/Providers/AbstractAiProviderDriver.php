<?php

namespace App\Core\Ai\Providers;

use App\Core\Ai\Contracts\AiProviderDriver;
use App\Core\Ai\Dto\AiChatCompletionResult;
use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Dto\AiTestResult;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Support\AiConfig;
use App\Core\Ai\Support\AiTaskPromptRegistry;
use App\Models\Ai\AiConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractAiProviderDriver implements AiProviderDriver
{
    protected function httpClient(AiConnection $connection): PendingRequest
    {
        $retryStatuses = config('ai.http.retry_on_status', [429, 500, 502, 503, 504]);

        return Http::baseUrl(rtrim($connection->base_url, '/'))
            ->timeout($connection->timeout)
            ->retry(
                $connection->retry_count,
                (int) config('ai.http.retry_backoff_ms', 500),
                function (Throwable $exception) use ($connection, $retryStatuses): bool {
                    if ($connection->retry_count <= 0) {
                        return false;
                    }

                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    if ($exception instanceof RequestException) {
                        return in_array($exception->response?->status(), $retryStatuses, true);
                    }

                    return false;
                },
            )
            ->acceptJson()
            ->withHeaders($this->additionalHeaders($connection));
    }

    /**
     * @return array<string, string>
     */
    protected function additionalHeaders(AiConnection $connection): array
    {
        return [];
    }

    protected function authorizeClient(AiConnection $connection, PendingRequest $client): PendingRequest
    {
        return $client->withToken($connection->api_key);
    }

    protected function extractRateLimitRemaining(Response $response): ?string
    {
        foreach ([
            'x-ratelimit-remaining',
            'x-ratelimit-remaining-requests',
            'x-rate-limit-remaining',
            'ratelimit-remaining',
        ] as $header) {
            if ($response->header($header)) {
                return (string) $response->header($header);
            }
        }

        return null;
    }

    protected function buildFailure(
        AiConnection $connection,
        int $responseTimeMs,
        string $message,
        ?Response $response = null,
    ): AiTestResult {
        if ($response !== null) {
            $body = trim($response->json('error.message') ?? $response->json('message') ?? $response->body());

            if ($body !== '') {
                $message = $message.': '.$body;
            }
        }

        return new AiTestResult(
            success: false,
            provider: $connection->provider,
            model: $connection->model,
            responseTimeMs: $responseTimeMs,
            rateLimitRemaining: $response ? $this->extractRateLimitRemaining($response) : null,
            errorMessage: Str::limit($message, 2000),
        );
    }

    protected function buildSuccess(
        AiConnection $connection,
        int $responseTimeMs,
        ?Response $response = null,
    ): AiTestResult {
        return new AiTestResult(
            success: true,
            provider: $connection->provider,
            model: $connection->model,
            responseTimeMs: $responseTimeMs,
            rateLimitRemaining: $response ? $this->extractRateLimitRemaining($response) : null,
        );
    }

    protected function testConnectionUsingPrompt(AiConnection $connection): AiTestResult
    {
        $resolved = AiTaskPromptRegistry::build(
            AiTaskRequest::make(
                taskType: AiTaskType::ConnectionTest,
                targetModule: AiTargetModule::Settings,
            ),
        );

        $completion = $this->completeChat($connection, [
            ['role' => 'system', 'content' => $resolved->system],
            ['role' => 'user', 'content' => $resolved->user],
        ], AiConfig::connectionTestMaxTokens());

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

    protected function unsupported(AiConnection $connection): AiTestResult
    {
        return new AiTestResult(
            success: false,
            provider: $connection->provider,
            model: $connection->model,
            responseTimeMs: 0,
            errorMessage: sprintf('%s driver is not implemented yet.', $connection->provider->label()),
        );
    }

    protected function guardThrowable(AiConnection $connection, Throwable $exception, int $responseTimeMs): AiTestResult
    {
        return new AiTestResult(
            success: false,
            provider: $connection->provider,
            model: $connection->model,
            responseTimeMs: $responseTimeMs,
            errorMessage: Str::limit($exception->getMessage(), 2000),
        );
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    protected function openAiCompatibleChat(
        AiConnection $connection,
        array $messages,
        ?int $maxTokens = null,
        array $extraHeaders = [],
        bool $jsonResponse = false,
    ): AiChatCompletionResult {
        $started = microtime(true);

        try {
            $client = $this->authorizeClient($connection, $this->httpClient($connection));

            if ($extraHeaders !== []) {
                $client = $client->withHeaders($extraHeaders);
            }

            $payload = [
                'model' => $connection->model,
                'messages' => $messages,
                'max_tokens' => $maxTokens ?? $connection->max_output_tokens,
                'temperature' => $connection->temperature,
            ];

            if ($jsonResponse) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = $client->post('/chat/completions', $payload);

            $elapsed = (int) round((microtime(true) - $started) * 1000);
            $usage = $this->extractUsage($response);

            if (! $response->successful()) {
                return new AiChatCompletionResult(
                    success: false,
                    content: null,
                    responseTimeMs: $elapsed,
                    tokensInput: $usage['input'],
                    tokensOutput: $usage['output'],
                    tokensTotal: $usage['total'],
                    estimatedCost: $usage['cost'],
                    errorMessage: Str::limit(trim($response->json('error.message') ?? $response->body()), 2000),
                    messages: $messages,
                );
            }

            $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            if ($content === '') {
                return new AiChatCompletionResult(
                    success: false,
                    content: null,
                    responseTimeMs: $elapsed,
                    tokensInput: $usage['input'],
                    tokensOutput: $usage['output'],
                    tokensTotal: $usage['total'],
                    estimatedCost: $usage['cost'],
                    errorMessage: 'Provider returned an empty response.',
                    messages: $messages,
                );
            }

            return new AiChatCompletionResult(
                success: true,
                content: $content,
                responseTimeMs: $elapsed,
                tokensInput: $usage['input'],
                tokensOutput: $usage['output'],
                tokensTotal: $usage['total'],
                estimatedCost: $usage['cost'],
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

    /**
     * @return array{input: ?int, output: ?int, total: ?int, cost: ?string}
     */
    protected function extractUsage(Response $response): array
    {
        $usage = $response->json('usage') ?? [];

        $input = isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null;
        $output = isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null;
        $total = isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null;

        $cost = data_get($response->json(), 'usage.cost')
            ?? data_get($response->json(), 'usage.total_cost')
            ?? data_get($response->json(), 'cost');

        return [
            'input' => $input,
            'output' => $output,
            'total' => $total,
            'cost' => $cost !== null ? (string) $cost : null,
        ];
    }

    protected function unsupportedChat(AiConnection $connection, array $messages): AiChatCompletionResult
    {
        return new AiChatCompletionResult(
            success: false,
            content: null,
            responseTimeMs: 0,
            errorMessage: sprintf('%s driver is not implemented yet.', $connection->provider->label()),
            messages: $messages,
        );
    }
}
