<?php

namespace App\Core\Ai\Services;

use App\Core\Ai\Dto\AiModelDefinition;
use App\Core\Ai\Dto\AiTestResult;
use App\Core\Ai\Support\AiConfig;
use App\Models\Ai\AiConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AiConnectionManager
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(array $data): AiConnection
    {
        return DB::transaction(function () use ($data): AiConnection {
            if ($data['is_default'] ?? false) {
                self::clearDefaultFlag();
            }

            return AiConnection::query()->create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function update(AiConnection $connection, array $data): AiConnection
    {
        return DB::transaction(function () use ($connection, $data): AiConnection {
            if ($data['is_default'] ?? false) {
                self::clearDefaultFlag($connection->id);
            }

            $connection->update($data);

            return $connection->refresh();
        });
    }

    public static function delete(AiConnection $connection): void
    {
        if ($connection->is_default && AiConnection::query()->where('is_default', true)->count() === 1) {
            throw ValidationException::withMessages([
                'connection' => 'The default AI connection cannot be deleted until another connection is set as default.',
            ]);
        }

        $connection->delete();
    }

    public static function recordTestResult(AiConnection $connection, AiTestResult $result): AiConnection
    {
        $connection->update([
            'last_tested_at' => now(),
            'last_test_status' => $result->status(),
            'last_test_response_time_ms' => $result->responseTimeMs,
            'last_test_rate_limit_remaining' => $result->rateLimitRemaining,
            'last_test_error' => $result->errorMessage,
        ]);

        return $connection->refresh();
    }

    /**
     * @param  list<AiModelDefinition>  $models
     */
    public static function storeAvailableModels(AiConnection $connection, array $models): AiConnection
    {
        $limit = AiConfig::maxStoredModels();
        $models = array_slice($models, 0, $limit);

        $connection->update([
            'available_models' => array_map(
                fn ($model) => $model->toArray(),
                $models,
            ),
        ]);

        return $connection->refresh();
    }

    protected static function clearDefaultFlag(?int $exceptId = null): void
    {
        $query = AiConnection::query()->where('is_default', true);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_default' => false]);
    }
}
