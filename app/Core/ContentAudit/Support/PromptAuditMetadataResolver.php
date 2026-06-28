<?php

namespace App\Core\ContentAudit\Support;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Services\AiPromptTemplateManager;
use App\Core\Ai\Services\AiService;
use App\Models\Ai\AiPromptTemplate;

final class PromptAuditMetadataResolver
{
    /**
     * @return array{
     *     template_name: string|null,
     *     template_version: string|null,
     *     ai_provider: string|null,
     *     model: string|null,
     * }
     */
    public static function resolve(AiTaskType $taskType, AiTargetModule $targetModule): array
    {
        $template = AiPromptTemplateManager::resolve($taskType, $targetModule);
        $connection = app(AiService::class)->defaultConnection();

        return [
            'template_name' => $template?->name,
            'template_version' => self::templateVersion($template),
            'ai_provider' => $connection?->provider->label(),
            'model' => $connection?->model,
        ];
    }

    protected static function templateVersion(?AiPromptTemplate $template): ?string
    {
        if (! $template?->updated_at) {
            return null;
        }

        return $template->updated_at->format('Y-m-d H:i').' UTC';
    }
}
