<?php

namespace Tests\Unit;

use App\Modules\Catalog\Support\BrandWebsiteFormatter;
use PHPUnit\Framework\TestCase;

class BrandWebsiteFormatterTest extends TestCase
{
    public function test_splits_full_https_www_url(): void
    {
        $parts = BrandWebsiteFormatter::split('https://www.kubota.com');

        $this->assertSame('https://www.', $parts['protocol']);
        $this->assertSame('kubota.com', $parts['domain']);
    }

    public function test_merges_protocol_and_domain(): void
    {
        $this->assertSame(
            'https://www.kubota.com',
            BrandWebsiteFormatter::merge('https://www.', 'kubota.com'),
        );
    }

    public function test_merge_strips_duplicate_protocol_from_domain(): void
    {
        $this->assertSame(
            'https://www.kubota.com',
            BrandWebsiteFormatter::merge('https://www.', 'https://www.kubota.com'),
        );
    }

    public function test_empty_domain_merges_to_null(): void
    {
        $this->assertNull(BrandWebsiteFormatter::merge('https://www.', ''));
    }

    public function test_supports_http_without_www(): void
    {
        $parts = BrandWebsiteFormatter::split('http://legacy.example.com');

        $this->assertSame('http://', $parts['protocol']);
        $this->assertSame('legacy.example.com', $parts['domain']);
    }
}
