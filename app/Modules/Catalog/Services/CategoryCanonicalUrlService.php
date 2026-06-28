<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryUrlRedirect;
use App\Models\User;
use Illuminate\Support\Str;

final class CategoryCanonicalUrlService
{
    /**
     * @param  array{name_en?: string, parent_id?: int|null, url_slug?: string|null, canonical_url?: string|null}  $previous
     */
    public static function syncAfterSave(Category $category, array $previous, ?User $changedBy = null, bool $isCreate = false): Category
    {
        $category->loadMissing('parent');

        if ($isCreate || self::shouldRegenerateUrls($category, $previous)) {
            self::applyGeneratedUrls($category, $previous, $changedBy);
            self::cascadeDescendantUrls($category, $changedBy);
        }

        return $category->refresh();
    }

    public static function buildCanonicalUrl(Category $category): string
    {
        $segments = self::pathSegments($category);

        return self::composeCanonicalUrl($segments);
    }

    public static function uniqueSlugSegment(string $nameEn, ?int $parentId, ?int $exceptCategoryId = null): string
    {
        $base = Str::slug($nameEn);

        if ($base === '') {
            $base = 'category';
        }

        $candidate = $base;
        $suffix = 2;

        while (self::slugTaken($parentId, $candidate, $exceptCategoryId)) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * @return list<string>
     */
    public static function pathSegments(Category $category): array
    {
        $chain = [];
        $node = $category;

        while ($node) {
            array_unshift($chain, $node);

            if ($node->parent_id && ! $node->relationLoaded('parent')) {
                $node->load('parent');
            }

            $node = $node->parent;
        }

        $segments = [];

        foreach ($chain as $link) {
            if (filled($link->url_slug)) {
                $segments[] = $link->url_slug;
            }
        }

        return $segments;
    }

    /**
     * @param  list<string>  $segments
     */
    public static function composeCanonicalUrl(array $segments): string
    {
        $base = rtrim((string) config('catalog.storefront_base_url'), '/');
        $prefix = trim((string) config('catalog.category_path_prefix', 'category'), '/');
        $path = $prefix;

        if ($segments !== []) {
            $path .= '/'.implode('/', $segments);
        }

        return $base.'/'.$path;
    }

    /**
     * @param  array{name_en?: string, parent_id?: int|null, url_slug?: string|null, canonical_url?: string|null}  $previous
     */
    protected static function shouldRegenerateUrls(Category $category, array $previous): bool
    {
        if (! filled($previous['canonical_url'] ?? null)) {
            return true;
        }

        if ((string) ($previous['name_en'] ?? '') !== $category->name_en) {
            return true;
        }

        if ((int) ($previous['parent_id'] ?? 0) !== (int) ($category->parent_id ?? 0)) {
            return true;
        }

        return false;
    }

    /**
     * @param  array{name_en?: string, parent_id?: int|null, url_slug?: string|null, canonical_url?: string|null}  $previous
     */
    protected static function resolveSlugSegment(Category $category, array $previous): string
    {
        $nameChanged = (string) ($previous['name_en'] ?? '') !== $category->name_en;
        $parentChanged = (int) ($previous['parent_id'] ?? 0) !== (int) ($category->parent_id ?? 0);

        if ($nameChanged || ! filled($category->url_slug)) {
            return self::uniqueSlugSegment($category->name_en, $category->parent_id, $category->id);
        }

        if ($parentChanged && self::slugTaken($category->parent_id, (string) $category->url_slug, $category->id)) {
            return self::uniqueSlugSegment($category->name_en, $category->parent_id, $category->id);
        }

        return (string) $category->url_slug;
    }

    /**
     * @param  array{name_en?: string, parent_id?: int|null, url_slug?: string|null, canonical_url?: string|null}  $previous
     */
    protected static function applyGeneratedUrls(Category $category, array $previous, ?User $changedBy): void
    {
        $oldCanonical = filled($previous['canonical_url'] ?? null)
            ? (string) $previous['canonical_url']
            : ($category->canonical_url ?? null);

        $category->url_slug = self::resolveSlugSegment($category, $previous);
        $newCanonical = self::buildCanonicalUrl($category);

        if (filled($oldCanonical) && $oldCanonical !== $newCanonical) {
            self::recordRedirect($category, $oldCanonical, $newCanonical, $changedBy);
        }

        $category->canonical_url = $newCanonical;
        $category->save();
    }

    protected static function recordRedirect(Category $category, string $oldUrl, string $newUrl, ?User $changedBy): void
    {
        CategoryUrlRedirect::query()->create([
            'category_id' => $category->id,
            'old_url' => $oldUrl,
            'new_url' => $newUrl,
            'redirect_status' => 301,
            'changed_at' => now(),
            'changed_by' => $changedBy?->id,
        ]);
    }

    protected static function cascadeDescendantUrls(Category $category, ?User $changedBy): void
    {
        $children = Category::query()
            ->where('parent_id', $category->id)
            ->orderBy('display_order')
            ->orderBy('name_en')
            ->get();

        foreach ($children as $child) {
            $previous = [
                'name_en' => $child->name_en,
                'parent_id' => $child->parent_id,
                'url_slug' => $child->url_slug,
                'canonical_url' => $child->canonical_url,
            ];

            $child->loadMissing('parent');
            self::applyGeneratedUrls($child, $previous, $changedBy);
            self::cascadeDescendantUrls($child, $changedBy);
        }
    }

    protected static function slugTaken(?int $parentId, string $slug, ?int $exceptCategoryId = null): bool
    {
        $query = Category::query()
            ->where('parent_id', $parentId)
            ->where('url_slug', $slug);

        if ($exceptCategoryId) {
            $query->where('id', '!=', $exceptCategoryId);
        }

        return $query->exists();
    }
}
