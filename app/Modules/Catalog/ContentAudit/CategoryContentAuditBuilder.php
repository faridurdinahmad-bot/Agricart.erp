<?php

namespace App\Modules\Catalog\ContentAudit;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\ContentAudit\Contracts\ContentAuditDocumentBuilder;
use App\Core\ContentAudit\Support\ContentAuditFieldFormatter;
use App\Core\ContentAudit\Support\PromptAuditMetadataResolver;
use App\Models\Catalog\Category;
use App\Modules\Catalog\ContentAudit\Support\CategoryImageMetadataResolver;
use App\Modules\Catalog\Services\CategoryImageStorage;
use App\Modules\Catalog\Services\CategoryManager;

final class CategoryContentAuditBuilder implements ContentAuditDocumentBuilder
{
    public function __construct(
        private readonly CategoryContentAuditAnalyzer $analyzer,
    ) {}

    public function module(): string
    {
        return 'catalog';
    }

    public function entity(): string
    {
        return 'category';
    }

    public function build(object $record): array
    {
        if (! $record instanceof Category) {
            throw new \InvalidArgumentException('CategoryContentAuditBuilder requires a Category model.');
        }

        $category = $record->loadMissing('parent');
        $hierarchy = CategoryManager::hierarchyChain($category);
        $prompt = PromptAuditMetadataResolver::resolve(
            AiTaskType::CategoryContent,
            AiTargetModule::Catalog,
        );
        $image = CategoryImageMetadataResolver::resolve($category->image_path);
        $analysis = $this->analyzer->analyze($category, $hierarchy);

        return [
            'meta' => [
                'export_type' => 'content_review',
                'module' => $this->module(),
                'entity' => $this->entity(),
                'entity_label' => $category->name_en,
                'entity_code' => $category->code,
                'exported_at' => now()->utc()->format('Y-m-d H:i').' UTC',
                'hierarchy_breadcrumb' => CategoryManager::hierarchyBreadcrumb($hierarchy),
            ],
            'structure' => [
                'level' => count($hierarchy),
                'children_count' => $category->children()->count(),
                'products_count' => 0,
                'products_module_connected' => false,
            ],
            'image' => [
                ...$image,
                'base64_data_uri' => CategoryImageMetadataResolver::base64DataUri($category->image_path),
            ],
            'summary' => $this->summaryFields($category, $hierarchy),
            'parent_hierarchy' => $hierarchy,
            'analysis' => $analysis,
            'review' => [
                'senior_review_notes' => null,
                'senior_review_notes_instructions' => 'Use this section for senior reviewer observations, approval decisions, and improvement actions.',
            ],
            'sections' => [
                $this->aiContentSection($category),
                $this->seoSection($category),
                $this->searchSection($category),
                $this->marketplaceSection($category),
                $this->promptSection($category, $prompt),
            ],
        ];
    }

    /**
     * @param  list<array{level: int, code: string, english_name: string, urdu_name: string|null, is_current: bool}>  $hierarchy
     * @return list<array{label: string, value: string|null}>
     */
    protected function summaryFields(Category $category, array $hierarchy): array
    {
        return [
            ContentAuditFieldFormatter::field('Category Code', $category->code),
            ContentAuditFieldFormatter::field('English Name', $category->name_en),
            ContentAuditFieldFormatter::field('Urdu Name', $category->name_ur),
            ContentAuditFieldFormatter::field('HS Code', $category->hs_code),
            ContentAuditFieldFormatter::field('Status', $category->is_active),
            ContentAuditFieldFormatter::field('Display Order', (string) $category->display_order),
            ContentAuditFieldFormatter::field('Category Level', (string) count($hierarchy)),
            ContentAuditFieldFormatter::field('Children Count', (string) $category->children()->count()),
            ContentAuditFieldFormatter::field('Products Count', '0 (Products module not connected)'),
            ContentAuditFieldFormatter::field('Image Path', $category->image_path),
            ContentAuditFieldFormatter::field('Image Filename', $category->image_path ? basename($category->image_path) : null),
            ContentAuditFieldFormatter::field('Image URL', CategoryImageStorage::url($category->image_path)),
            ContentAuditFieldFormatter::field('AI Status', $category->aiStatusLabel()),
            ContentAuditFieldFormatter::field('AI Model', $category->last_ai_model),
            ContentAuditFieldFormatter::field('Last AI Generated', $category->lastAiGeneratedLabel()),
        ];
    }

    /**
     * @return array{title: string, groups: list<array{title: string, fields: list<array{label: string, value: string|null}>}>}
     */
    protected function aiContentSection(Category $category): array
    {
        return [
            'title' => 'AI Content',
            'groups' => [
                [
                    'title' => 'Descriptions',
                    'fields' => [
                        ContentAuditFieldFormatter::field('Short Description (English)', $category->short_description_en),
                        ContentAuditFieldFormatter::field('Short Description (Urdu)', $category->short_description_ur),
                        ContentAuditFieldFormatter::field('Long Description (English)', $category->long_description_en),
                        ContentAuditFieldFormatter::field('Long Description (Urdu)', $category->long_description_ur),
                    ],
                ],
                [
                    'title' => 'Usage & Benefits',
                    'fields' => [
                        ContentAuditFieldFormatter::field('Usage (English)', $category->usage_en),
                        ContentAuditFieldFormatter::field('Usage (Urdu)', $category->usage_ur),
                        ContentAuditFieldFormatter::field('Benefits (English)', $category->benefits_en),
                        ContentAuditFieldFormatter::field('Benefits (Urdu)', $category->benefits_ur),
                        ContentAuditFieldFormatter::field('Warnings (English)', $category->warnings_en),
                        ContentAuditFieldFormatter::field('Warnings (Urdu)', $category->warnings_ur),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function seoSection(Category $category): array
    {
        return [
            'title' => 'SEO',
            'fields' => [
                ContentAuditFieldFormatter::field('SEO Title', $category->seo_title),
                ContentAuditFieldFormatter::field('Focus Keyword (English)', $category->seo_focus_keyword_en),
                ContentAuditFieldFormatter::field('Focus Keyword (Urdu)', $category->seo_focus_keyword_ur),
                ContentAuditFieldFormatter::field('Meta Description', $category->meta_description),
                ContentAuditFieldFormatter::field('Meta Keywords', $category->meta_keywords),
                ContentAuditFieldFormatter::field('URL Slug', $category->url_slug),
                ContentAuditFieldFormatter::field('Canonical URL', $category->canonical_url),
                ContentAuditFieldFormatter::field('Meta Robots', $category->meta_robots),
                ContentAuditFieldFormatter::field('Open Graph Title', $category->og_title),
                ContentAuditFieldFormatter::field('Open Graph Description', $category->og_description),
            ],
        ];
    }

    /**
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function searchSection(Category $category): array
    {
        return [
            'title' => 'Search',
            'fields' => [
                ContentAuditFieldFormatter::field('Synonyms (English)', $category->synonyms_en),
                ContentAuditFieldFormatter::field('Synonyms (Urdu)', $category->synonyms_ur),
                ContentAuditFieldFormatter::field('Alternate Spellings', $category->alternate_spellings),
                ContentAuditFieldFormatter::field('Search Aliases', $category->search_aliases),
                ContentAuditFieldFormatter::field('Internal Tags', $category->internal_tags),
            ],
        ];
    }

    /**
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function marketplaceSection(Category $category): array
    {
        return [
            'title' => 'Marketplace',
            'fields' => [
                ContentAuditFieldFormatter::field('Google Category', $category->google_category),
                ContentAuditFieldFormatter::field('Facebook Category', $category->facebook_category),
            ],
        ];
    }

    /**
     * @param  array{
     *     template_name: string|null,
     *     template_version: string|null,
     *     ai_provider: string|null,
     *     model: string|null,
     * }  $prompt
     * @return array{title: string, fields: list<array{label: string, value: string|null}>}
     */
    protected function promptSection(Category $category, array $prompt): array
    {
        return [
            'title' => 'Prompt Information',
            'fields' => [
                ContentAuditFieldFormatter::field('Prompt Template Name', $prompt['template_name']),
                ContentAuditFieldFormatter::field('Prompt Version', $prompt['template_version']),
                ContentAuditFieldFormatter::field('AI Provider', $prompt['ai_provider']),
                ContentAuditFieldFormatter::field('Model', $prompt['model']),
                ContentAuditFieldFormatter::field('Record AI Model Used', $category->last_ai_model),
                ContentAuditFieldFormatter::field('Record AI Prompt Override', $category->ai_prompt_override),
            ],
        ];
    }
}
