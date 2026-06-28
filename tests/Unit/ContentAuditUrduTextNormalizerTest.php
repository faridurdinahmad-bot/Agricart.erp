<?php

namespace Tests\Unit;

use App\Core\ContentAudit\Support\ContentAuditUrduTextNormalizer;
use Tests\TestCase;

class ContentAuditUrduTextNormalizerTest extends TestCase
{
    public function test_normalizer_removes_superscript_alef_and_zero_width_chars(): void
    {
        $input = 'زرعی'.mb_chr(0x0670, 'UTF-8').' مشین'.mb_chr(0x200B, 'UTF-8').'ری';
        $normalized = ContentAuditUrduTextNormalizer::normalize($input);

        $this->assertNotNull($normalized);
        $this->assertStringNotContainsString(mb_chr(0x0670, 'UTF-8'), $normalized);
        $this->assertStringNotContainsString(mb_chr(0x200B, 'UTF-8'), $normalized);
        $this->assertStringContainsString('زرعی', $normalized);
        $this->assertStringContainsString('مشین', $normalized);
    }

    public function test_normalizer_returns_null_for_empty_input(): void
    {
        $this->assertNull(ContentAuditUrduTextNormalizer::normalize(null));
        $this->assertNull(ContentAuditUrduTextNormalizer::normalize(''));
        $this->assertNull(ContentAuditUrduTextNormalizer::normalize('   '));
    }
}
