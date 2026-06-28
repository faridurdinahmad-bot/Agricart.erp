<?php

namespace App\Modules\Catalog\Services;

use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Services\AiContentStatusManager;
use App\Core\Ai\Services\AiService;
use App\Core\Ai\Support\AiResponseParser;
use App\Models\Catalog\Category;
use App\Modules\Catalog\Dto\CategoryAiGenerationResult;
use App\Modules\Catalog\Support\CategoryAiContentSchema;
use Illuminate\Support\Str;

final class CategoryAiContentService
{
    public function __construct(
        private readonly AiService $aiService,
        private readonly AiResponseParser $responseParser,
    ) {}

    /**
     * @param  array<string, mixed>  $formState
     */
    public function generate(array $formState, ?Category $category = null): CategoryAiGenerationResult
    {
        $nameEn = trim((string) ($formState['english_name'] ?? ''));

        if ($nameEn === '') {
            return CategoryAiGenerationResult::failed('English name is required before generating AI content.');
        }

        $taskResult = $this->aiService->run(
            AiTaskRequest::make(
                taskType: AiTaskType::CategoryContent,
                targetModule: AiTargetModule::Catalog,
                context: [
                    'name_en' => $nameEn,
                    'hs_code' => trim((string) ($formState['hs_code'] ?? '')),
                    'ai_prompt_override' => trim((string) ($formState['ai_prompt_override'] ?? '')),
                ],
                targetType: $category ? 'catalog_category' : null,
                targetId: $category?->id,
            ),
        );

        if (! $taskResult->success || blank($taskResult->content)) {
            $this->markFailedIfPersisted($category);

            return CategoryAiGenerationResult::failed(
                $taskResult->errorMessage ?? 'AI provider did not return a response.',
            );
        }

        $parseResult = $this->responseParser->parse(
            $taskResult->content,
            CategoryAiContentSchema::fields(),
        );

        if (! $parseResult->success) {
            $this->markFailedIfPersisted($category);

            return CategoryAiGenerationResult::failed($parseResult->errorSummary());
        }

        $formFields = $this->mapToFormFields($parseResult->data);
        $connection = $this->aiService->connection();
        $modelLabel = $connection?->model ?? 'unknown';

        if ($category) {
            CategoryManager::applyAiGeneratedContent($category, $formFields, $modelLabel);
        }

        $generatedLabel = now()->format('d M Y, H:i').' · '.$modelLabel;

        return CategoryAiGenerationResult::succeeded($formFields, $modelLabel, $generatedLabel);
    }

    /**
     * @param  array<string, string>  $parsed
     * @return array<string, string>
     */
    protected function mapToFormFields(array $parsed): array
    {
        $formFields = [];

        foreach (CategoryAiContentSchema::formFieldMap() as $jsonKey => $formKey) {
            if (! array_key_exists($jsonKey, $parsed)) {
                continue;
            }

            $value = $parsed[$jsonKey];

            if ($formKey === 'url_slug') {
                $value = Str::slug($value);
            }

            $formFields[$formKey] = $value;
        }

        return $formFields;
    }

    protected function markFailedIfPersisted(?Category $category): void
    {
        if ($category) {
            AiContentStatusManager::markFailed($category);
        }
    }
}
