<?php

namespace Tests\Unit;

use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Support\AiPromptVariableRegistry;
use App\Modules\Catalog\Support\BrandAiContentNormalizer;
use PHPUnit\Framework\TestCase;

class BrandAiContentNormalizerTest extends TestCase
{
    public function test_replaces_platform_name_in_descriptions_with_canonical_brand(): void
    {
        $normalized = BrandAiContentNormalizer::normalize('Ak', [
            'urdu_name' => 'ایگری کارڈ ای آر پی',
            'short_description_en' => 'Agricart ERP offers reliable hardware products.',
            'short_description_ur' => 'ایگری کارڈ قابل اعتماد مصنوعات پیش کرتا ہے۔',
        ]);

        $this->assertSame('اے کے', $normalized['urdu_name']);
        $this->assertStringContainsString('Ak', $normalized['short_description_en']);
        $this->assertStringNotContainsString('Agricart', $normalized['short_description_en']);
        $this->assertStringContainsString('اے', $normalized['short_description_ur']);
        $this->assertStringNotContainsString('ایگری کارڈ', $normalized['short_description_ur']);
    }

    public function test_does_not_rewrite_true_agricart_brand(): void
    {
        $fields = [
            'urdu_name' => 'ایگری کارڈ',
            'short_description_en' => 'Agricart ERP platform tools.',
        ];

        $this->assertSame(
            $fields,
            BrandAiContentNormalizer::normalize('Agricart', $fields),
        );
    }

    public function test_latin_brand_name_to_urdu_script(): void
    {
        $this->assertSame('اے کے', BrandAiContentNormalizer::latinBrandNameToUrduScript('Ak'));
        $this->assertSame('این ایس کے', BrandAiContentNormalizer::latinBrandNameToUrduScript('NSK'));
    }
}

class BrandAiPromptContextTest extends TestCase
{
    public function test_brand_context_does_not_alias_category_to_english_name(): void
    {
        $values = AiPromptVariableRegistry::enrichContext(AiTaskType::BrandContent, [
            'name_en' => 'Ak',
            'short_note' => 'Hardware brand',
            'category' => '',
            'assigned_categories' => '',
        ]);

        $this->assertSame('Ak', $values['english_name']);
        $this->assertSame('Ak', $values['brand']);
        $this->assertSame('', $values['category']);
    }
}
