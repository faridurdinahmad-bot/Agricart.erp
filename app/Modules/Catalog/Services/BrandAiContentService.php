<?php

namespace App\Modules\Catalog\Services;

use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Services\AiContentStatusManager;
use App\Core\Ai\Services\AiService;
use App\Core\Ai\Support\AiResponseParser;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\Dto\BrandAiGenerationResult;
use App\Modules\Catalog\Support\BrandAiContentNormalizer;
use App\Modules\Catalog\Support\BrandAiContentSchema;

final class BrandAiContentService
{
    public function __construct(
        private readonly AiService $aiService,
        private readonly AiResponseParser $responseParser,
    ) {}

    /**
     * @param  array<string, mixed>  $formState
     */
    public function generate(array $formState, ?Brand $brand = null): BrandAiGenerationResult
    {
        $nameEn = trim((string) ($formState['english_name'] ?? ''));

        if ($nameEn === '') {
            return BrandAiGenerationResult::failed('English name is required before generating AI content.');
        }

        $assignedCategories = $this->assignedCategoryNames($formState);

        $taskResult = $this->aiService->run(
            AiTaskRequest::make(
                taskType: AiTaskType::BrandContent,
                targetModule: AiTargetModule::Catalog,
                context: [
                    'english_name' => $nameEn,
                    'name_en' => $nameEn,
                    'brand' => $nameEn,
                    'short_note' => trim((string) ($formState['short_note'] ?? '')),
                    'category' => $assignedCategories !== [] ? implode(', ', $assignedCategories) : '',
                    'assigned_categories' => implode(', ', $assignedCategories),
                ],
                targetType: $brand ? 'catalog_brand' : null,
                targetId: $brand?->id,
            ),
        );

        if (! $taskResult->success || blank($taskResult->content)) {
            $this->markFailedIfPersisted($brand);

            return BrandAiGenerationResult::failed(
                $taskResult->errorMessage ?? 'AI provider did not return a response.',
            );
        }

        $parseResult = $this->responseParser->parse(
            $taskResult->content,
            BrandAiContentSchema::fields(),
        );

        if (! $parseResult->success) {
            $this->markFailedIfPersisted($brand);

            return BrandAiGenerationResult::failed($parseResult->errorSummary());
        }

        $formFields = BrandAiContentNormalizer::normalize(
            $nameEn,
            $this->mapToFormFields($parseResult->data),
        );
        $connection = $this->aiService->connection();
        $modelLabel = $connection?->model ?? 'unknown';

        if ($brand) {
            BrandManager::applyAiGeneratedContent($brand, $formFields, $modelLabel);
        }

        $generatedLabel = now()->format('d M Y, H:i').' · '.$modelLabel;

        return BrandAiGenerationResult::succeeded($formFields, $modelLabel, $generatedLabel);
    }

    /**
     * @param  array<string, string>  $parsed
     * @return array<string, string>
     */
    protected function mapToFormFields(array $parsed): array
    {
        $formFields = [];

        foreach (BrandAiContentSchema::formFieldMap() as $jsonKey => $formKey) {
            if (! array_key_exists($jsonKey, $parsed)) {
                continue;
            }

            $value = trim($parsed[$jsonKey]);

            if ($value === '' && in_array($jsonKey, BrandAiContentSchema::uncertainOptionalKeys(), true)) {
                continue;
            }

            $formFields[$formKey] = $value;
        }

        return $formFields;
    }

    /**
     * @param  array<string, mixed>  $formState
     * @return list<string>
     */
    protected function assignedCategoryNames(array $formState): array
    {
        $ids = array_filter(array_map(
            fn ($id): int => (int) $id,
            (array) ($formState['category_ids'] ?? []),
        ));

        if ($ids === []) {
            return [];
        }

        return \App\Models\Catalog\Category::query()
            ->whereIn('id', $ids)
            ->orderBy('name_en')
            ->pluck('name_en')
            ->all();
    }

    protected function markFailedIfPersisted(?Brand $brand): void
    {
        if ($brand) {
            AiContentStatusManager::markFailed($brand);
        }
    }
}
