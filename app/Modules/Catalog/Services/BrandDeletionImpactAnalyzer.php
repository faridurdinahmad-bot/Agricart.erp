<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog\Brand;
use App\Modules\Catalog\Dto\BrandDeletionImpact;

final class BrandDeletionImpactAnalyzer
{
    public static function analyze(Brand $brand): BrandDeletionImpact
    {
        $assignedCategoriesCount = $brand->categories()->count();
        $productsCount = self::productsCount($brand);
        $hasAiContent = self::hasAiContent($brand);

        $blockers = [];
        $warnings = [];

        if ($productsCount > 0) {
            $blockers[] = 'This brand has assigned products. Reassign products before approving deletion.';
        }

        if ($assignedCategoriesCount > 0) {
            $warnings[] = "{$assignedCategoriesCount} categor".($assignedCategoriesCount === 1 ? 'y is' : 'ies are').' assigned to this brand.';
        }

        if ($hasAiContent) {
            $warnings[] = 'AI-generated content is stored on this brand.';
        }

        if (filled($brand->logo_path)) {
            $warnings[] = 'A brand logo is attached.';
        }

        return new BrandDeletionImpact(
            assignedCategoriesCount: $assignedCategoriesCount,
            productsCount: $productsCount,
            hasAiContent: $hasAiContent,
            canApprove: $blockers === [],
            blockers: $blockers,
            warnings: $warnings,
        );
    }

    protected static function productsCount(Brand $brand): int
    {
        return 0;
    }

    protected static function hasAiContent(Brand $brand): bool
    {
        foreach (BrandManager::aiContentFieldKeys() as $field) {
            if (filled($brand->{$field})) {
                return true;
            }
        }

        return filled($brand->name_ur)
            || filled($brand->last_ai_generated_at);
    }
}
