<?php

namespace App\Modules\Catalog\Support;

final class BrandAiContentNormalizer
{
    /**
     * @param  array<string, string>  $formFields
     * @return array<string, string>
     */
    public static function normalize(string $canonicalEnglishName, array $formFields): array
    {
        if (self::isAgricartBrandName($canonicalEnglishName)) {
            return $formFields;
        }

        $replacements = self::platformNameReplacements($canonicalEnglishName);

        foreach ($formFields as $key => $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            foreach ($replacements as $from => $to) {
                $value = self::replaceInsensitive($value, $from, $to);
            }

            $formFields[$key] = $value;
        }

        $urduName = trim((string) ($formFields['urdu_name'] ?? ''));

        if ($urduName !== '' && self::containsPlatformName($urduName)) {
            $formFields['urdu_name'] = self::latinBrandNameToUrduScript($canonicalEnglishName);
        }

        return $formFields;
    }

    /**
     * @return array<string, string>
     */
    protected static function platformNameReplacements(string $canonicalEnglishName): array
    {
        return [
            'Agricart ERP' => $canonicalEnglishName,
            'Agri Cart ERP' => $canonicalEnglishName,
            'Agri-Cart ERP' => $canonicalEnglishName,
            'Agricart' => $canonicalEnglishName,
            'Agri Cart' => $canonicalEnglishName,
            'Agri-Cart' => $canonicalEnglishName,
            'ایگری کارڈ ای آر پی' => self::latinBrandNameToUrduScript($canonicalEnglishName),
            'ایگری کارڈ' => self::latinBrandNameToUrduScript($canonicalEnglishName),
            'ایگریکارت' => self::latinBrandNameToUrduScript($canonicalEnglishName),
        ];
    }

    public static function isAgricartBrandName(string $englishName): bool
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '', $englishName) ?? '');

        return in_array($normalized, ['agricart', 'agricartpk', 'agricartpkerp'], true)
            || str_contains($normalized, 'agricart');
    }

    public static function containsPlatformName(string $value): bool
    {
        $needles = [
            'agricart',
            'agri cart',
            'agri-cart',
            'ایگری کارڈ',
            'ایگریکارت',
        ];

        foreach ($needles as $needle) {
            if (self::containsInsensitive($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function latinBrandNameToUrduScript(string $englishName): string
    {
        $map = [
            'A' => 'اے',
            'B' => 'بی',
            'C' => 'سی',
            'D' => 'ڈی',
            'E' => 'ای',
            'F' => 'ایف',
            'G' => 'جی',
            'H' => 'ایچ',
            'I' => 'آئی',
            'J' => 'جے',
            'K' => 'کے',
            'L' => 'ایل',
            'M' => 'ایم',
            'N' => 'این',
            'O' => 'او',
            'P' => 'پی',
            'Q' => 'کیو',
            'R' => 'آر',
            'S' => 'ایس',
            'T' => 'ٹی',
            'U' => 'یو',
            'V' => 'وی',
            'W' => 'ڈبلیو',
            'X' => 'ایکس',
            'Y' => 'وائی',
            'Z' => 'زی',
        ];

        $parts = preg_split('/[\s\-_]+/', trim($englishName)) ?: [trim($englishName)];
        $segments = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $letters = [];

            foreach (mb_str_split(mb_strtoupper($part)) as $char) {
                if (isset($map[$char])) {
                    $letters[] = $map[$char];
                } elseif (preg_match('/^\p{L}$/u', $char) === 1) {
                    $letters[] = $char;
                }
            }

            if ($letters !== []) {
                $segments[] = implode(' ', $letters);
            }
        }

        return implode(' ', $segments);
    }

    protected static function replaceInsensitive(string $haystack, string $needle, string $replacement): string
    {
        if ($needle === '') {
            return $haystack;
        }

        return preg_replace('/'.preg_quote($needle, '/').'/iu', $replacement, $haystack) ?? $haystack;
    }

    protected static function containsInsensitive(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        return preg_match('/'.preg_quote($needle, '/').'/iu', $haystack) === 1;
    }
}
