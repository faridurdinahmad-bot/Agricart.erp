<?php

namespace App\Core\ContentAudit\Support;

use Normalizer;

final class ContentAuditUrduTextNormalizer
{
    /**
     * Normalize Urdu/Arabic script for reliable PDF rendering.
     */
    public static function normalize(?string $text): ?string
    {
        if (! filled($text)) {
            return null;
        }

        $text = trim($text);

        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($text, Normalizer::FORM_C);

            if (is_string($normalized)) {
                $text = $normalized;
            }
        }

        // Remove zero-width and bidi control characters.
        $text = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $text) ?? $text;

        // Remove superscript alef (U+0670) — common AI artifact causing broken glyphs.
        $text = str_replace(mb_chr(0x0670, 'UTF-8'), '', $text);

        // Collapse whitespace while preserving paragraph breaks.
        $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
