<?php

namespace Tests\Unit;

use App\Modules\Catalog\Support\BrandLogoSpec;
use PHPUnit\Framework\TestCase;

class BrandLogoSpecTest extends TestCase
{
    public function test_allows_only_webp_mime_type(): void
    {
        $this->assertTrue(BrandLogoSpec::isAllowedMimeType('image/webp'));
        $this->assertFalse(BrandLogoSpec::isAllowedMimeType('image/jpeg'));
        $this->assertFalse(BrandLogoSpec::isAllowedMimeType('image/png'));
        $this->assertFalse(BrandLogoSpec::isAllowedMimeType('image/gif'));
        $this->assertFalse(BrandLogoSpec::isAllowedMimeType('image/svg+xml'));
        $this->assertFalse(BrandLogoSpec::isAllowedMimeType('application/pdf'));
    }

    public function test_allows_only_webp_extension(): void
    {
        $this->assertTrue(BrandLogoSpec::isAllowedExtension('webp'));
        $this->assertFalse(BrandLogoSpec::isAllowedExtension('jpg'));
        $this->assertFalse(BrandLogoSpec::isAllowedExtension('jpeg'));
        $this->assertFalse(BrandLogoSpec::isAllowedExtension('png'));
        $this->assertFalse(BrandLogoSpec::isAllowedExtension('gif'));
        $this->assertFalse(BrandLogoSpec::isAllowedExtension('bmp'));
    }

    public function test_accept_attribute_uses_webp_only(): void
    {
        $accept = BrandLogoSpec::acceptAttribute();

        $this->assertSame('.webp,image/webp', $accept);
        $this->assertStringNotContainsString('image/jpeg', $accept);
        $this->assertStringNotContainsString('image/*', $accept);
    }

    public function test_upload_hint_mentions_webp_only(): void
    {
        $hint = BrandLogoSpec::uploadHint();

        $this->assertStringContainsString('WebP only', $hint);
        $this->assertStringContainsString('Max 5 MB', $hint);
    }
}
