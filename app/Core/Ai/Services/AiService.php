<?php

namespace App\Core\Ai\Services;

use App\Core\Ai\AiProviderFactory;
use App\Core\Ai\Dto\AiModelDefinition;
use App\Core\Ai\Dto\AiResolvedPrompt;
use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Dto\AiTaskResult;
use App\Core\Ai\Dto\AiTestResult;
use App\Core\Ai\Support\AiConfig;
use App\Core\Ai\Support\AiTaskPromptRegistry;
use App\Models\Ai\AiConnection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Central AI entry point for all Agricart modules.
 * Never call provider APIs directly outside this service layer.
 */
final class AiService
{
    /**
     * @return list<AiModelDefinition>
     */
    public function fetchModels(AiConnection $connection): array
    {
        if (! $connection->provider->isImplemented()) {
            throw new InvalidArgumentException(sprintf(
                '%s is not available in this release.',
                $connection->provider->label(),
            ));
        }

        return AiProviderFactory::make($connection->provider)->fetchModels($connection);
    }

    /**
     * @param  array{
     *     provider: string,
     *     api_key: string,
     *     base_url: string,
     *     model: string,
     *     context_window?: int,
     *     max_output_tokens?: int,
     *     temperature?: float,
     *     timeout?: int,
     *     retry_count?: int,
     * }  $config
     * @return list<AiModelDefinition>
     */
    public function fetchModelsFromConfig(array $config): array
    {
        return $this->fetchModels($this->temporaryConnection($config));
    }

    public function testConnection(AiConnection $connection): AiTestResult
    {
        if (! $connection->provider->isImplemented()) {
            $result = new AiTestResult(
                success: false,
                provider: $connection->provider,
                model: $connection->model,
                responseTimeMs: 0,
                errorMessage: sprintf('%s driver is not implemented yet.', $connection->provider->label()),
            );
        } else {
            $result = AiProviderFactory::make($connection->provider)
                ->testConnection($connection);
        }

        if ($connection->exists) {
            AiConnectionManager::recordTestResult($connection, $result);
            AiJobHistoryManager::recordFromTestResult($connection, $result);
        }

        if (! $result->success) {
            $this->logFailure('connection_test', $connection, $result->errorMessage);
        }

        return $result;
    }

    /**
     * @param  array{
     *     provider: string,
     *     api_key: string,
     *     base_url: string,
     *     model: string,
     *     context_window?: int,
     *     max_output_tokens?: int,
     *     temperature?: float,
     *     timeout?: int,
     *     retry_count?: int,
     * }  $config
     */
    public function testConfig(array $config): AiTestResult
    {
        return AiProviderFactory::makeFromString($config['provider'])
            ->testConnection($this->temporaryConnection($config));
    }

    /**
     * Run a typed AI task. All modules must use this method with an AiTaskType.
     */
    public function run(AiTaskRequest $request): AiTaskResult
    {
        $connection = $this->connection($request->connectionId);

        if (! $connection) {
            return $this->failedTaskResult(
                request: $request,
                connection: null,
                responseTimeMs: 0,
                errorMessage: 'No active AI connection is configured.',
            );
        }

        if (! $connection->is_active) {
            return $this->failedTaskResult(
                request: $request,
                connection: $connection,
                responseTimeMs: 0,
                errorMessage: 'The selected AI connection is inactive.',
            );
        }

        if (! $connection->provider->isImplemented()) {
            return $this->failedTaskResult(
                request: $request,
                connection: $connection,
                responseTimeMs: 0,
                errorMessage: sprintf('%s is not available in this release.', $connection->provider->label()),
            );
        }

        try {
            $resolved = AiTaskPromptRegistry::build($request);
            $connection = $this->connectionWithPromptOverrides($connection, $resolved);

            $messages = [
                ['role' => 'system', 'content' => $resolved->system],
                ['role' => 'user', 'content' => $resolved->user],
            ];

            $completion = AiProviderFactory::make($connection->provider)
                ->completeChat(
                    $connection,
                    $messages,
                    $resolved->maxOutputTokens,
                    $resolved->jsonResponse,
                );

            $taskResult = new AiTaskResult(
                success: $completion->success,
                content: $completion->content,
                responseTimeMs: $completion->responseTimeMs,
                tokensInput: $completion->tokensInput,
                tokensOutput: $completion->tokensOutput,
                tokensTotal: $completion->tokensTotal,
                estimatedCost: $completion->estimatedCost,
                errorMessage: $completion->errorMessage,
            );

            if (! $taskResult->success) {
                $this->logFailure($request->taskType->value, $connection, $taskResult->errorMessage);
            }

            if ($connection->exists) {
                $job = AiJobHistoryManager::recordFromTaskRun($connection, $request, $taskResult);

                return new AiTaskResult(
                    success: $taskResult->success,
                    content: $taskResult->content,
                    responseTimeMs: $taskResult->responseTimeMs,
                    tokensInput: $taskResult->tokensInput,
                    tokensOutput: $taskResult->tokensOutput,
                    tokensTotal: $taskResult->tokensTotal,
                    estimatedCost: $taskResult->estimatedCost,
                    errorMessage: $taskResult->errorMessage,
                    job: $job,
                );
            }

            return $taskResult;
        } catch (InvalidArgumentException $exception) {
            return $this->failedTaskResult(
                request: $request,
                connection: $connection,
                responseTimeMs: 0,
                errorMessage: $exception->getMessage(),
            );
        } catch (Throwable $exception) {
            $this->logFailure($request->taskType->value, $connection, $exception->getMessage());

            return $this->failedTaskResult(
                request: $request,
                connection: $connection,
                responseTimeMs: 0,
                errorMessage: 'An unexpected AI error occurred. Check application logs for details.',
            );
        }
    }

    public function defaultConnection(): ?AiConnection
    {
        return AiConnection::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first()
            ?? AiConnection::query()
                ->where('is_active', true)
                ->orderByDesc('last_tested_at')
                ->first();
    }

    public function connection(?int $id = null): ?AiConnection
    {
        if ($id) {
            return AiConnection::query()->find($id);
        }

        return $this->defaultConnection();
    }

    /**
     * @return Collection<int, AiConnection>
     */
    public function activeConnections(): Collection
    {
        return AiConnection::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('provider')
            ->get();
    }

    protected function failedTaskResult(
        AiTaskRequest $request,
        ?AiConnection $connection,
        int $responseTimeMs,
        string $errorMessage,
    ): AiTaskResult {
        $this->logFailure($request->taskType->value, $connection, $errorMessage);

        $taskResult = new AiTaskResult(
            success: false,
            content: null,
            responseTimeMs: $responseTimeMs,
            errorMessage: $errorMessage,
        );

        if ($connection?->exists) {
            $job = AiJobHistoryManager::recordFromTaskRun($connection, $request, $taskResult);

            return new AiTaskResult(
                success: false,
                content: null,
                responseTimeMs: $responseTimeMs,
                errorMessage: $errorMessage,
                job: $job,
            );
        }

        return $taskResult;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function temporaryConnection(array $config): AiConnection
    {
        $defaults = AiConfig::connectionDefaults();

        $connection = new AiConnection([
            'provider' => $config['provider'],
            'api_key' => $config['api_key'],
            'base_url' => $config['base_url'],
            'model' => $config['model'],
            'context_window' => (int) ($config['context_window'] ?? $defaults['context_window']),
            'max_output_tokens' => (int) ($config['max_output_tokens'] ?? $defaults['max_output_tokens']),
            'temperature' => (float) ($config['temperature'] ?? $defaults['temperature']),
            'timeout' => (int) ($config['timeout'] ?? $defaults['timeout']),
            'retry_count' => (int) ($config['retry_count'] ?? $defaults['retry_count']),
            'is_active' => true,
            'is_default' => false,
        ]);

        $connection->exists = false;

        return $connection;
    }

    protected function connectionWithPromptOverrides(AiConnection $connection, AiResolvedPrompt $resolved): AiConnection
    {
        if ($resolved->maxOutputTokens === null && $resolved->temperature === null) {
            return $connection;
        }

        $overridden = $connection->replicate();
        $overridden->exists = $connection->exists;
        $overridden->id = $connection->id;

        if ($resolved->maxOutputTokens !== null) {
            $overridden->max_output_tokens = $resolved->maxOutputTokens;
        }

        if ($resolved->temperature !== null) {
            $overridden->temperature = $resolved->temperature;
        }

        return $overridden;
    }

    protected function logFailure(string $taskType, ?AiConnection $connection, ?string $errorMessage): void
    {
        Log::warning('ai.task.failed', [
            'task_type' => $taskType,
            'provider' => $connection?->provider->value,
            'connection_id' => $connection?->id,
            'model' => $connection?->model,
            'error' => $errorMessage,
        ]);
    }
}
