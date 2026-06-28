<?php

namespace Tests\Unit;

use App\Core\ContentAudit\Enums\ContentAuditFormat;
use App\Core\ContentAudit\Services\ContentAuditExportService;
use App\Core\ContentAudit\Support\ContentAuditJsonRenderer;
use App\Core\ContentAudit\Support\ContentAuditMarkdownRenderer;
use App\Core\ContentAudit\Support\ContentAuditPdfRenderer;
use App\Core\ContentAudit\Support\ContentAuditPdfTypography;
use App\Models\Catalog\Category;
use App\Modules\Catalog\ContentAudit\CategoryContentAuditAnalyzer;
use App\Modules\Catalog\ContentAudit\CategoryContentAuditBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryContentAuditExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_audit_document_includes_review_sections(): void
    {
        $category = Category::query()->create([
            'code' => 'CAT-9001',
            'name_en' => 'Irrigation Pumps',
            'name_ur' => 'ирригация',
            'hs_code' => '8424.81',
            'display_order' => 3,
            'is_active' => true,
            'short_description_en' => 'Short EN',
            'seo_title' => 'Buy Irrigation Pumps',
            'url_slug' => 'irrigation-pumps',
            'google_category' => 'Home & Garden > Irrigation',
        ]);

        $builder = new CategoryContentAuditBuilder(new CategoryContentAuditAnalyzer);
        $document = $builder->build($category);

        $this->assertSame('content_review', $document['meta']['export_type']);
        $this->assertArrayHasKey('analysis', $document);
        $this->assertArrayHasKey('structure', $document);
        $this->assertArrayHasKey('image', $document);
        $this->assertArrayHasKey('review', $document);
        $this->assertSame('Prompt Information', $document['sections'][4]['title']);

        $json = app(ContentAuditJsonRenderer::class)->render($document);
        $this->assertStringContainsString('Irrigation Pumps', $json);
        $this->assertStringNotContainsString('base64_data_uri', $json);
        $this->assertStringNotContainsString('api_key', strtolower($json));

        $markdown = app(ContentAuditMarkdownRenderer::class)->render($document);
        $this->assertStringContainsString('## Missing Fields Summary', $markdown);
        $this->assertStringContainsString('## AI Review Checklist', $markdown);
        $this->assertStringContainsString('## Senior Review Notes', $markdown);
    }

    public function test_export_service_supports_pdf_filename(): void
    {
        $service = app(ContentAuditExportService::class);

        $filename = $service->filename([
            'meta' => [
                'entity' => 'category',
                'entity_code' => 'CAT-9001',
            ],
        ], ContentAuditFormat::Pdf);

        $this->assertStringEndsWith('.pdf', $filename);
    }

    public function test_pdf_renderer_produces_binary_output(): void
    {
        $category = Category::query()->create([
            'code' => 'CAT-9002',
            'name_en' => 'Test Category',
            'name_ur' => 'ٹیسٹ',
            'is_active' => true,
        ]);

        $builder = new CategoryContentAuditBuilder(new CategoryContentAuditAnalyzer);
        $document = $builder->build($category);

        $pdf = app(ContentAuditPdfRenderer::class)->render($document);

        $this->assertNotSame('', $pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_pdf_renderer_supports_long_urdu_paragraphs_with_arabic_font(): void
    {
        $longUrdu = 'زرعی مشینری اور پرزہ جات اس زمرے میں ٹریکٹر، ہارویسٹر، کلٹیویٹر، بیج بونے کی مشینیں، '
            .'فصل کی کٹائی کے اوزار، آبپاشی کے پمپ اور دیگر زرعی آلات شامل ہیں۔ '
            .'یہ مصنوعات کسانوں کو زمین کی تیاری، بیج بونا، فصل کی دیکھ بھال اور کٹائی کے مراحل میں مدد دیتی ہیں۔ '
            .'معیاری پرزے اور مناسب دیکھ بھال سے مشینوں کی کارکردگی بہتر رہتی ہے اور پیداوار میں اضافہ ہوتا ہے۔';

        $category = Category::query()->create([
            'code' => 'CAT-9003',
            'name_en' => 'Agricultural Machinery And Parts',
            'name_ur' => 'زرعی مشینری اور پرزہ جات',
            'short_description_ur' => $longUrdu,
            'long_description_ur' => $longUrdu,
            'usage_ur' => 'کھیت کی تیاری، بیج بونا، آبپاشی اور فصل کی کٹائی',
            'benefits_ur' => 'وقت کی بچت، یکساں کام، بہتر پیداوار',
            'warnings_ur' => 'آلات استعمال کرنے سے پہلے ہدایات پڑھیں اور حفاظتی اوزار پہنیں',
            'seo_focus_keyword_ur' => 'زرعی مشینری',
            'synonyms_ur' => 'ٹریکٹر، ہارویسٹر، زرعی اوزار',
            'is_active' => true,
        ]);

        $builder = new CategoryContentAuditBuilder(new CategoryContentAuditAnalyzer);
        $document = $builder->build($category);

        $pdf = app(ContentAuditPdfRenderer::class)->render($document);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(50000, strlen($pdf));
        $this->assertStringContainsString('NotoNaskhArabic', $pdf);

        $pages = app(ContentAuditPdfRenderer::class)->pageCount($document);
        $this->assertLessThanOrEqual(3, $pages, 'Large category PDF should stay within 3 pages.');

        $outputPath = storage_path('app/testing/category-review-urdu.pdf');
        if (! is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }
        file_put_contents($outputPath, $pdf);
    }

    public function test_pdf_typography_detects_urdu_fields(): void
    {
        $this->assertTrue(ContentAuditPdfTypography::isUrduFieldLabel('Short Description (Urdu)'));
        $this->assertTrue(ContentAuditPdfTypography::containsArabicScript('زرعی مشینری'));
        $this->assertStringContainsString('pdf-urdu', ContentAuditPdfTypography::urduSpan('ٹیسٹ'));
        $this->assertSame('—', ContentAuditPdfTypography::emptyPlaceholder('Urdu Name'));
        $this->assertSame(
            'Will be generated automatically',
            ContentAuditPdfTypography::emptyPlaceholder('Short Description (English)'),
        );
    }

    public function test_normal_category_pdf_fits_within_two_pages(): void
    {
        $category = Category::query()->create([
            'code' => 'CAT-9010',
            'name_en' => 'Irrigation Pumps',
            'name_ur' => 'آبپاشی پمپ',
            'short_description_en' => 'Efficient pumps for farm irrigation systems.',
            'short_description_ur' => 'زرعی آبپاشی کے لیے موثر پمپ۔',
            'seo_title' => 'Buy Irrigation Pumps',
            'meta_description' => 'Quality irrigation pumps for Pakistani farms.',
            'url_slug' => 'irrigation-pumps',
            'is_active' => true,
        ]);

        $builder = new CategoryContentAuditBuilder(new CategoryContentAuditAnalyzer);
        $document = $builder->build($category);

        $pages = app(ContentAuditPdfRenderer::class)->pageCount($document);

        $this->assertGreaterThanOrEqual(1, $pages);
        $this->assertLessThanOrEqual(3, $pages, 'Normal category review should stay within 3 pages.');
    }
}
