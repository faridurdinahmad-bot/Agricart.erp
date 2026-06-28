<?php

namespace Tests\Unit;

use App\Modules\Catalog\Support\CatalogWebpImageSpec;
use PHPUnit\Framework\TestCase;

class CatalogWebpImageSpecTest extends TestCase
{
    public function test_allows_only_webp(): void
    {
        $this->assertTrue(CatalogWebpImageSpec::isAllowedMimeType('image/webp'));
        $this->assertTrue(CatalogWebpImageSpec::isAllowedExtension('webp'));
        $this->assertFalse(CatalogWebpImageSpec::isAllowedMimeType('image/jpeg'));
        $this->assertFalse(CatalogWebpImageSpec::isAllowedExtension('png'));
    }

    public function test_accept_attribute_is_webp_only(): void
    {
        $this->assertSame('.webp,image/webp', CatalogWebpImageSpec::acceptAttribute());
    }

    public function test_upload_hint_is_brief(): void
    {
        $hint = CatalogWebpImageSpec::uploadHint();

        $this->assertStringContainsString('WebP only', $hint);
        $this->assertStringContainsString('Max 5 MB', $hint);
        $this->assertStringNotContainsString('400', $hint);
    }
}
