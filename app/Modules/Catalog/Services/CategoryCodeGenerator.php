<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog\Category;

final class CategoryCodeGenerator
{
    public static function next(): string
    {
        $latestNumeric = Category::query()
            ->where('code', 'like', 'CAT-%')
            ->pluck('code')
            ->map(function (string $code): int {
                if (preg_match('/^CAT-(\d+)$/', $code, $matches) !== 1) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return 'CAT-'.str_pad((string) ($latestNumeric + 1), 6, '0', STR_PAD_LEFT);
    }
}
