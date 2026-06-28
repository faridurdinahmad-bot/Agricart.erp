<?php

namespace App\Modules\Catalog\ContentAudit;

use App\Core\Ai\Enums\AiContentStatus;
use App\Models\Catalog\Brand;

final class BrandContentAuditAnalyzer
{
    /**
     * @return array{
     *     missing_fields: list<array{field: string, label: string, section: string, severity: string}>,
     *     content_quality: list<array{metric: string, value: string, status: string}>,
     *     ai_review_checklist: list<array{item: string, status: string, detail: string|null}>,
     * }
     */
    public function analyze(Brand $brand): array
    {
        return [
            'missing_fields' => $this->missingFields($brand),
            'content_quality' => $this->contentQuality($brand),
            'ai_review_checklist' => $this->aiReviewChecklist($brand),
        ];
    }

    /**
     * @return list<array{field: string, label: string, section: string, severity: string}>
     */
    protected function missingFields(Brand $brand): array
    {
        $checks = [
            ['field' => 'name_en', 'label' => 'English Name', 'section' => 'Identity', 'severity' => 'critical', 'value' => $brand->name_en],
            ['field' => 'name_ur', 'label' => 'Urdu Name', 'section' => 'Identity', 'severity' => 'critical', 'value' => $brand->name_ur],
            ['field' => 'logo_path', 'label' => 'Brand Logo', 'section' => 'Identity', 'severity' => 'recommended', 'value' => $brand->logo_path],
            ['field' => 'short_description_en', 'label' => 'Short Description (English)', 'section' => 'Descriptions', 'severity' => 'recommended', 'value' => $brand->short_description_en],
            ['field' => 'short_description_ur', 'label' => 'Short Description (Urdu)', 'section' => 'Descriptions', 'severity' => 'recommended', 'value' => $brand->short_description_ur],
            ['field' => 'brand_overview_en', 'label' => 'Brand Overview (English)', 'section' => 'Descriptions', 'severity' => 'recommended', 'value' => $brand->brand_overview_en],
            ['field' => 'seo_title', 'label' => 'SEO Title', 'section' => 'SEO', 'severity' => 'recommended', 'value' => $brand->seo_title],
            ['field' => 'seo_description', 'label' => 'SEO Description', 'section' => 'SEO', 'severity' => 'recommended', 'value' => $brand->seo_description],
        ];

        $missing = [];

        foreach ($checks as $check) {
            if (filled($check['value'])) {
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
    protected function contentQuality(Brand $brand): array
    {
        return [
            ['metric' => 'AI Status', 'value' => $brand->aiStatusLabel(), 'status' => $brand->ai_content_status === AiContentStatus::Complete ? 'good' : 'attention'],
            ['metric' => 'Assigned Categories', 'value' => (string) $brand->categories()->count(), 'status' => $brand->categories()->count() > 0 ? 'good' : 'attention'],
            ['metric' => 'Short Note', 'value' => filled($brand->short_note) ? 'Provided' : 'Missing', 'status' => filled($brand->short_note) ? 'good' : 'attention'],
        ];
    }

    /**
     * @return list<array{item: string, status: string, detail: string|null}>
     */
    protected function aiReviewChecklist(Brand $brand): array
    {
        return [
            ['item' => 'English and Urdu names present', 'status' => filled($brand->name_en) && filled($brand->name_ur) ? 'pass' : 'fail', 'detail' => null],
            ['item' => 'Descriptions reviewed', 'status' => filled($brand->short_description_en) && filled($brand->short_description_ur) ? 'pass' : 'review', 'detail' => null],
            ['item' => 'SEO fields populated', 'status' => filled($brand->seo_title) ? 'pass' : 'review', 'detail' => null],
            ['item' => 'Country/website only if confident', 'status' => 'pass', 'detail' => 'Leave empty when uncertain — do not guess.'],
        ];
    }
}
