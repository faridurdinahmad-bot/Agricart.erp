<?php

namespace App\Modules\Catalog\ContentAudit;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\ContentAudit\Contracts\ContentAuditDocumentBuilder;
use App\Core\ContentAudit\Support\ContentAuditFieldFormatter;
use App\Core\ContentAudit\Support\PromptAuditMetadataResolver;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\ContentAudit\Support\BrandImageMetadataResolver;
use App\Modules\Catalog\Services\BrandImageStorage;

final class BrandContentAuditBuilder implements ContentAuditDocumentBuilder
{
    public function __construct(
        private readonly BrandContentAuditAnalyzer $analyzer,
    ) {}

    public function module(): string
    {
        return 'catalog';
    }

    public function entity(): string
    {
        return 'brand';
    }

    public function build(object $record): array
    {
        if (! $record instanceof Brand) {
            throw new \InvalidArgumentException('BrandContentAuditBuilder requires a Brand model.');
        }

        $brand = $record->loadMissing('categories');
        $prompt = PromptAuditMetadataResolver::resolve(
            AiTaskType::BrandContent,
            AiTargetModule::Catalog,
        );
        $image = BrandImageMetadataResolver::resolve($brand->logo_path);
        $analysis = $this->analyzer->analyze($brand);

        return [
            'meta' => [
                'export_type' => 'content_review',
                'module' => $this->module(),
                'entity' => $this->entity(),
                'entity_label' => $brand->name_en,
                'entity_code' => $brand->code,
                'exported_at' => now()->utc()->format('Y-m-d H:i').' UTC',
            ],
            'structure' => [
                'assigned_categories_count' => $brand->categories->count(),
                'products_count' => 0,
                'products_module_connected' => false,
            ],
            'image' => [
                ...$image,
                'base64_data_uri' => BrandImageMetadataResolver::base64DataUri($brand->logo_path),
            ],
            'summary' => $this->summaryFields($brand),
            'assigned_categories' => $brand->categories->map(fn ($category): array => [
                'code' => $category->code,
                'name_en' => $category->name_en,
                'name_ur' => $category->name_ur,
            ])->all(),
            'analysis' => $analysis,
            'review' => [
                'senior_review_notes' => null,
                'senior_review_notes_instructions' => 'Use this section for senior reviewer observations, approval decisions, and improvement actions.',
            ],
            'sections' => [
                $this->descriptionsSection($brand),
                $this->seoSection($brand),
                $this->companySection($brand),
                $this->promptSection($brand, $prompt),
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: string|null}>
     */
    protected function summaryFields(Brand $brand): array
    {
        return [
            ContentAuditFieldFormatter::field('Brand Code', $brand->code),
            ContentAuditFieldFormatter::field('English Name', $brand->name_en),
            ContentAuditFieldFormatter::field('Urdu Name', $brand->name_ur),
            ContentAuditFieldFormatter::field('Short Note', $brand->short_note),
            ContentAuditFieldFormatter::field('Status', $brand->is_active),
            ContentAuditFieldFormatter::field('Logo Path', $brand->logo_path),
            ContentAuditFieldFormatter::field('Logo URL', BrandImageStorage::url($brand->logo_path)),
            ContentAuditFieldFormatter::field('AI Status', $brand->aiStatusLabel()),
            ContentAuditFieldFormatter::field('AI Model', $brand->last_ai_model),
            ContentAuditFieldFormatter::field('Last AI Generated', $brand->lastAiGeneratedLabel()),
            ContentAuditFieldFormatter::field('Content Reviewed', $brand->contentReviewedLabel()),
        ];
    }

    /**
     * @return array{title: string, groups: list<array{title: string, fields: list<array{label: string, value: string|null}>}>}
     */
    protected function descriptionsSection(Brand $brand): array
    {
        return [
            'title' => 'Descriptions',
            'groups' => [
                [
                    'title' => 'Content',
                    'fields' => [
                        ContentAuditFieldFormatter::field('Short Description (English)', $brand->short_description_en),
                        ContentAuditFieldFormatter::field('Short Description (Urdu)', $brand->short_description_ur),
                        ContentAuditFieldFormatter::field('Long Description (English)', $brand->long_description_en),
                        ContentAuditFieldFormatter::field('Long Description (Urdu)', $brand->long_description_ur),
                        ContentAuditFieldFormatter::field('Brand Overview (English)', $brand->brand_overview_en),
                        ContentAuditFieldFormatter::field('Brand Overview (Urdu)', $brand->brand_overview_ur),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function seoSection(Brand $brand): array
    {
        return [
            'title' => 'SEO',
            'fields' => [
                ContentAuditFieldFormatter::field('SEO Title', $brand->seo_title),
                ContentAuditFieldFormatter::field('SEO Description', $brand->seo_description),
                ContentAuditFieldFormatter::field('SEO Keywords', $brand->seo_keywords),
            ],
        ];
    }

    /**
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function companySection(Brand $brand): array
    {
        return [
            'title' => 'Company Information',
            'fields' => [
                ContentAuditFieldFormatter::field('Country', $brand->country),
                ContentAuditFieldFormatter::field('Website', $brand->website),
            ],
        ];
    }

    /**
     * @param  array<string, string|null>  $prompt
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function promptSection(Brand $brand, array $prompt): array
    {
        return [
            'title' => 'Prompt Information',
            'fields' => [
                ContentAuditFieldFormatter::field('Task Type', $prompt['task_type'] ?? null),
                ContentAuditFieldFormatter::field('Template', $prompt['template_name'] ?? null),
                ContentAuditFieldFormatter::field('Short Note Used', $brand->short_note),
                ContentAuditFieldFormatter::field('Last AI Model', $brand->last_ai_model),
            ],
        ];
    }
}
