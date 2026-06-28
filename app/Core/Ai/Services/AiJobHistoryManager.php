<?php

namespace App\Core\Ai\Services;

use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Dto\AiTaskResult;
use App\Core\Ai\Dto\AiTestResult;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Models\Ai\AiConnection;
use App\Models\Ai\AiJobHistory;

final class AiJobHistoryManager
{
    public static function recordFromTestResult(
        AiConnection $connection,
        AiTestResult $result,
        ?int $userId = null,
    ): AiJobHistory {
        return self::record([
            'user_id' => $userId ?? auth()->id(),
            'ai_connection_id' => $connection->id,
            'provider' => $connection->provider->value,
            'model' => $connection->model,
            'task_type' => AiTaskType::ConnectionTest->value,
            'target_module' => AiTargetModule::Settings->value,
            'target_type' => 'ai_connection',
            'target_id' => $connection->id,
            'success' => $result->success,
            'response_time_ms' => $result->responseTimeMs,
            'error_message' => $result->errorMessage,
            'context_snapshot' => [
                'headline' => $result->headline(),
            ],
        ]);
    }

    public static function recordFromTaskRun(
        AiConnection $connection,
        AiTaskRequest $request,
        AiTaskResult $result,
    ): AiJobHistory {
        return self::record([
            'user_id' => $request->userId ?? auth()->id(),
            'ai_connection_id' => $connection->id,
            'provider' => $connection->provider->value,
            'model' => $connection->model,
            'task_type' => $request->taskType->value,
            'target_module' => $request->targetModule->value,
            'target_type' => $request->targetType,
            'target_id' => $request->targetId,
            'success' => $result->success,
            'response_time_ms' => $result->responseTimeMs,
            'tokens_input' => $result->tokensInput,
            'tokens_output' => $result->tokensOutput,
            'tokens_total' => $result->tokensTotal,
            'estimated_cost' => $result->estimatedCost,
            'error_message' => $result->errorMessage,
            'context_snapshot' => self::sanitizeContext($request->context),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function record(array $attributes): AiJobHistory
    {
        return AiJobHistory::query()->create([
            ...$attributes,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected static function sanitizeContext(array $context): array
    {
        $maxLength = (int) config('ai.tasks.context_snapshot_max_string_length', 500);

        return self::sanitizeValue($context, $maxLength);
    }

    /**
     * @return array<string, mixed>|string|int|float|bool|null
     */
    protected static function sanitizeValue(mixed $value, int $maxLength): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $item) {
                if (self::isSensitiveKey((string) $key)) {
                    continue;
                }

                $sanitized[$key] = self::sanitizeValue($item, $maxLength);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    protected static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (['api_key', 'apikey', 'password', 'token', 'secret', 'authorization', 'credential'] as $blocked) {
            if ($normalized === $blocked || str_contains($normalized, $blocked)) {
                return true;
            }
        }

        return false;
    }
}
