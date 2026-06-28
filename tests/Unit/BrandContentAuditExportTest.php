<?php

namespace Tests\Unit;

use App\Core\ContentAudit\Enums\ContentAuditFormat;
use App\Core\ContentAudit\Services\ContentAuditExportService;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\ContentAudit\BrandContentAuditAnalyzer;
use App\Modules\Catalog\ContentAudit\BrandContentAuditBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandContentAuditExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_audit_document_includes_review_sections(): void
    {
        $brand = Brand::query()->create([
            'code' => 'BR-9001',
            'name_en' => 'Kubota',
            'name_ur' => 'کوبوتا',
            'short_note' => 'Japanese agricultural machinery manufacturer',
            'is_active' => true,
            'short_description_en' => 'Short EN',
            'seo_title' => 'Buy Kubota',
        ]);

        $builder = new BrandContentAuditBuilder(new BrandContentAuditAnalyzer);
        $document = $builder->build($brand);

        $this->assertSame('content_review', $document['meta']['export_type']);
        $this->assertSame('brand', $document['meta']['entity']);
        $this->assertArrayHasKey('analysis', $document);
        $this->assertArrayHasKey('review', $document);
        $this->assertSame('Prompt Information', $document['sections'][3]['title']);
    }

    public function test_export_service_supports_brand_pdf_filename(): void
    {
        $service = app(ContentAuditExportService::class);

        $filename = $service->filename([
            'meta' => [
                'entity' => 'brand',
                'entity_code' => 'BR-9001',
            ],
        ], ContentAuditFormat::Pdf);

        $this->assertStringEndsWith('.pdf', $filename);
        $this->assertStringStartsWith('brand-', $filename);
    }
}
