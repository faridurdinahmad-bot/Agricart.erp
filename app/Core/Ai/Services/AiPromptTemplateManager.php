<?php

namespace App\Core\Ai\Services;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Support\AiPromptVariableRegistry;
use App\Models\Ai\AiPromptTemplate;

final class AiPromptTemplateManager
{
    public static function resolve(AiTaskType $taskType, AiTargetModule $targetModule): ?AiPromptTemplate
    {
        return AiPromptTemplate::query()
            ->where('task_type', $taskType->value)
            ->where('is_active', true)
            ->where(function ($query) use ($targetModule): void {
                $query
                    ->where('target_module', $targetModule->value)
                    ->orWhere('target_module', AiTargetModule::System->value);
            })
            ->orderByRaw(
                'CASE WHEN target_module = ? THEN 0 ELSE 1 END',
                [$targetModule->value],
            )
            ->orderByDesc('updated_at')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(array $data): AiPromptTemplate
    {
        $data = self::normalizePayload($data);

        return AiPromptTemplate::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function update(AiPromptTemplate $template, array $data): AiPromptTemplate
    {
        $data = self::normalizePayload($data, $template);
        $template->update($data);

        return $template->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizePayload(array $data, ?AiPromptTemplate $existing = null): array
    {
        $taskType = $data['task_type'] instanceof AiTaskType
            ? $data['task_type']
            : AiTaskType::from((string) $data['task_type']);

        $data['available_variables'] = $data['available_variables']
            ?? AiPromptVariableRegistry::forTaskType($taskType);

        if (($data['temperature'] ?? null) === '' || $data['temperature'] === null) {
            $data['temperature'] = null;
        }

        if (($data['max_output_tokens'] ?? null) === '' || $data['max_output_tokens'] === null) {
            $data['max_output_tokens'] = null;
        }

        return $data;
    }
}
