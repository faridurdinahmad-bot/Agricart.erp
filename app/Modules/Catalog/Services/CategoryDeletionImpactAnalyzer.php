<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog\Category;
use App\Modules\Catalog\Dto\CategoryDeletionImpact;

final class CategoryDeletionImpactAnalyzer
{
    public static function analyze(Category $category): CategoryDeletionImpact
    {
        $directChildrenCount = $category->children()->count();
        $descendantsCount = count(CategoryManager::descendantIdsFor($category->id));
        $productsCount = self::productsCount($category);
        $redirectsCount = $category->urlRedirects()->count();
        $hasAiContent = self::hasAiContent($category);

        $blockers = [];
        $warnings = [];

        if ($directChildrenCount > 0 || $descendantsCount > 0) {
            $blockers[] = 'This category has child categories. Reassign or delete children before approving deletion.';
        }

        if ($productsCount > 0) {
            $blockers[] = 'This category has assigned products. Reassign products before approving deletion.';
        }

        if ($redirectsCount > 0) {
            $warnings[] = "{$redirectsCount} URL redirect record(s) exist for this category.";
        }

        if ($hasAiContent) {
            $warnings[] = 'AI-generated content is stored on this category.';
        }

        if (filled($category->image_path)) {
            $warnings[] = 'A category image is attached.';
        }

        return new CategoryDeletionImpact(
            directChildrenCount: $directChildrenCount,
            descendantsCount: $descendantsCount,
            productsCount: $productsCount,
            redirectsCount: $redirectsCount,
            hasAiContent: $hasAiContent,
            canApprove: $blockers === [],
            blockers: $blockers,
            warnings: $warnings,
        );
    }

    protected static function productsCount(Category $category): int
    {
        return 0;
    }

    protected static function hasAiContent(Category $category): bool
    {
        foreach (CategoryManager::aiContentFieldKeys() as $field) {
            if (filled($category->{$field})) {
                return true;
            }
        }

        return filled($category->name_ur)
            || filled($category->last_ai_generated_at);
    }
}
