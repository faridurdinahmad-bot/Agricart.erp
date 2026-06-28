<?php

namespace App\Modules\Catalog\ContentAudit;

use App\Core\Ai\Enums\AiContentStatus;
use App\Models\Catalog\Category;

final class CategoryContentAuditAnalyzer
{
    /**
     * @param  list<array{level: int, code: string, english_name: string, urdu_name: string|null, is_current: bool}>  $hierarchy
     * @return array{
     *     missing_fields: list<array{field: string, label: string, section: string, severity: string}>,
     *     content_quality: list<array{metric: string, value: string, status: string}>,
     *     ai_review_checklist: list<array{item: string, status: string, detail: string|null}>,
     * }
     */
    public function analyze(Category $category, array $hierarchy): array
    {
        return [
            'missing_fields' => $this->missingFields($category),
            'content_quality' => $this->contentQuality($category),
            'ai_review_checklist' => $this->aiReviewChecklist($category),
        ];
    }

    /**
     * @return list<array{field: string, label: string, section: string, severity: string}>
     */
    protected function missingFields(Category $category): array
    {
        $checks = [
            ['field' => 'name_en', 'label' => 'English Name', 'section' => 'Identity', 'severity' => 'critical', 'value' => $category->name_en],
            ['field' => 'name_ur', 'label' => 'Urdu Name', 'section' => 'Identity', 'severity' => 'critical', 'value' => $category->name_ur],
            ['field' => 'image_path', 'label' => 'Category Image', 'section' => 'Identity', 'severity' => 'recommended', 'value' => $category->image_path],
            ['field' => 'short_description_en', 'label' => 'Short Description (English)', 'section' => 'AI Content', 'severity' => 'recommended', 'value' => $category->short_description_en],
            ['field' => 'short_description_ur', 'label' => 'Short Description (Urdu)', 'section' => 'AI Content', 'severity' => 'recommended', 'value' => $category->short_description_ur],
            ['field' => 'long_description_en', 'label' => 'Long Description (English)', 'section' => 'AI Content', 'severity' => 'recommended', 'value' => $category->long_description_en],
            ['field' => 'long_description_ur', 'label' => 'Long Description (Urdu)', 'section' => 'AI Content', 'severity' => 'recommended', 'value' => $category->long_description_ur],
            ['field' => 'usage_en', 'label' => 'Usage (English)', 'section' => 'AI Content', 'severity' => 'optional', 'value' => $category->usage_en],
            ['field' => 'usage_ur', 'label' => 'Usage (Urdu)', 'section' => 'AI Content', 'severity' => 'optional', 'value' => $category->usage_ur],
            ['field' => 'benefits_en', 'label' => 'Benefits (English)', 'section' => 'AI Content', 'severity' => 'optional', 'value' => $category->benefits_en],
            ['field' => 'benefits_ur', 'label' => 'Benefits (Urdu)', 'section' => 'AI Content', 'severity' => 'optional', 'value' => $category->benefits_ur],
            ['field' => 'seo_title', 'label' => 'SEO Title', 'section' => 'SEO', 'severity' => 'recommended', 'value' => $category->seo_title],
            ['field' => 'meta_description', 'label' => 'Meta Description', 'section' => 'SEO', 'severity' => 'recommended', 'value' => $category->meta_description],
            ['field' => 'url_slug', 'label' => 'URL Slug', 'section' => 'SEO', 'severity' => 'recommended', 'value' => $category->url_slug],
            ['field' => 'seo_focus_keyword_en', 'label' => 'Focus Keyword (English)', 'section' => 'SEO', 'severity' => 'optional', 'value' => $category->seo_focus_keyword_en],
            ['field' => 'seo_focus_keyword_ur', 'label' => 'Focus Keyword (Urdu)', 'section' => 'SEO', 'severity' => 'optional', 'value' => $category->seo_focus_keyword_ur],
            ['field' => 'synonyms_en', 'label' => 'Synonyms (English)', 'section' => 'Search', 'severity' => 'optional', 'value' => $category->synonyms_en],
            ['field' => 'search_aliases', 'label' => 'Search Aliases', 'section' => 'Search', 'severity' => 'optional', 'value' => $category->search_aliases],
            ['field' => 'google_category', 'label' => 'Google Category', 'section' => 'Marketplace', 'severity' => 'optional', 'value' => $category->google_category],
            ['field' => 'facebook_category', 'label' => 'Facebook Category', 'section' => 'Marketplace', 'severity' => 'optional', 'value' => $category->facebook_category],
        ];

        $missing = [];

        foreach ($checks as $check) {
            if (filled($check['value'])) {
                continue;
            }

            if ($check['severity'] === 'optional') {
                continue;
            }

            $missing[] = [
                'field' => $check['field'],
                'label' => $check['label'],
                'section' => $check['section'],
                'severity' => $check['severity'],
            ];
        }

        return $missing;
    }

    /**
     * @return list<array{metric: string, value: string, status: string}>
     */
    protected function contentQuality(Category $category): array
    {
        $shortEn = str_word_count(strip_tags((string) $category->short_description_en));
        $shortUr = mb_strlen(trim((string) $category->short_description_ur));
        $longEn = str_word_count(strip_tags((string) $category->long_description_en));
        $seoScore = $this->seoCompletenessScore($category);
        $bilingualPairs = $this->bilingualPairsFilled($category);

        return [
            [
                'metric' => 'Short Description Length (English words)',
                'value' => (string) $shortEn,
                'status' => $shortEn >= 20 ? 'good' : ($shortEn > 0 ? 'warn' : 'bad'),
            ],
            [
                'metric' => 'Long Description Length (English words)',
                'value' => (string) $longEn,
                'status' => $longEn >= 50 ? 'good' : ($longEn > 0 ? 'warn' : 'bad'),
            ],
            [
                'metric' => 'Urdu Short Description Present',
                'value' => $shortUr > 0 ? 'Yes' : 'No',
                'status' => $shortUr > 0 ? 'good' : 'bad',
            ],
            [
                'metric' => 'Bilingual Field Parity',
                'value' => $bilingualPairs['filled'].' / '.$bilingualPairs['total'].' pairs complete',
                'status' => $bilingualPairs['filled'] === $bilingualPairs['total'] ? 'good' : ($bilingualPairs['filled'] > 0 ? 'warn' : 'bad'),
            ],
            [
                'metric' => 'SEO Completeness',
                'value' => $seoScore.'%',
                'status' => $seoScore >= 80 ? 'good' : ($seoScore >= 50 ? 'warn' : 'bad'),
            ],
            [
                'metric' => 'AI Content Status',
                'value' => $category->aiStatusLabel(),
                'status' => match ($category->ai_content_status) {
                    AiContentStatus::Complete => 'good',
                    AiContentStatus::NeedsReview => 'warn',
                    AiContentStatus::AiFailed => 'bad',
                    default => 'warn',
                },
            ],
        ];
    }

    /**
     * @return list<array{item: string, status: string, detail: string|null}>
     */
    protected function aiReviewChecklist(Category $category): array
    {
        $seoTitleLen = mb_strlen((string) $category->seo_title);
        $metaLen = mb_strlen((string) $category->meta_description);
        $slugValid = filled($category->url_slug) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $category->url_slug);
        $urduHasScript = filled($category->name_ur) && preg_match('/[\x{0600}-\x{06FF}]/u', (string) $category->name_ur);

        return [
            $this->checklistItem('English and Urdu names are present', filled($category->name_en) && filled($category->name_ur)),
            $this->checklistItem('Urdu name uses Urdu script', $urduHasScript, 'Urdu name should not be transliterated English.'),
            $this->checklistItem('Category image uploaded', filled($category->image_path)),
            $this->checklistItem('Short descriptions provided (EN/UR)', filled($category->short_description_en) && filled($category->short_description_ur)),
            $this->checklistItem('Long descriptions provided (EN/UR)', filled($category->long_description_en) && filled($category->long_description_ur)),
            $this->checklistItem('SEO title within 60 characters', $seoTitleLen > 0 && $seoTitleLen <= 60, $seoTitleLen > 60 ? "Current length: {$seoTitleLen}" : null),
            $this->checklistItem('Meta description within 160 characters', $metaLen > 0 && $metaLen <= 160, $metaLen > 160 ? "Current length: {$metaLen}" : null),
            $this->checklistItem('URL slug is lowercase with hyphens', (bool) $slugValid),
            $this->checklistItem('AI content marked complete', $category->ai_content_status === AiContentStatus::Complete),
            $this->checklistItem('Marketplace categories configured', filled($category->google_category) || filled($category->facebook_category)),
            $this->checklistItem('Search aliases or synonyms defined', filled($category->search_aliases) || filled($category->synonyms_en)),
        ];
    }

    /**
     * @return array{item: string, status: string, detail: string|null}
     */
    protected function checklistItem(string $item, bool $passed, ?string $detail = null): array
    {
        return [
            'item' => $item,
            'status' => $passed ? 'pass' : 'fail',
            'detail' => $passed ? null : $detail,
        ];
    }

    protected function seoCompletenessScore(Category $category): int
    {
        $fields = [
            $category->seo_title,
            $category->meta_description,
            $category->url_slug,
            $category->seo_focus_keyword_en,
            $category->og_title,
            $category->canonical_url,
        ];

        $filled = count(array_filter($fields, fn ($value) => filled($value)));

        return (int) round(($filled / count($fields)) * 100);
    }

    /**
     * @return array{filled: int, total: int}
     */
    protected function bilingualPairsFilled(Category $category): array
    {
        $pairs = [
            [$category->short_description_en, $category->short_description_ur],
            [$category->long_description_en, $category->long_description_ur],
            [$category->usage_en, $category->usage_ur],
            [$category->benefits_en, $category->benefits_ur],
        ];

        $filled = 0;

        foreach ($pairs as [$english, $urdu]) {
            if (filled($english) && filled($urdu)) {
                $filled++;
            }
        }

        return ['filled' => $filled, 'total' => count($pairs)];
    }
}
