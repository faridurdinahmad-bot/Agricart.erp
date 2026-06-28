<?php

namespace App\Modules\Catalog\Services;

use App\Core\Ai\Services\AiContentStatusManager;
use App\Models\Catalog\Category;
use App\Models\User;
use App\Modules\Catalog\Support\CategoryAiContentSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class CategoryManager
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(array $data, ?UploadedFile $image = null): Category
    {
        self::assertUniqueNameUnderParent($data['name_en'], $data['parent_id'] ?? null);

        $category = Category::query()->create([
            ...self::mappedAttributes($data),
            'code' => CategoryCodeGenerator::next(),
        ]);

        if ($image) {
            $category->update([
                'image_path' => CategoryImageStorage::store($category, $image),
            ]);
        }

        AiContentStatusManager::markPendingIfAiFieldsEmpty($category->refresh(), self::aiContentFieldKeys());

        return $category->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        self::assertUniqueNameUnderParent(
            $data['name_en'],
            $data['parent_id'] ?? null,
            $category->id,
        );

        self::assertValidParent($category, $data['parent_id'] ?? null);

        $category->update(self::mappedAttributes($data));

        if ($image) {
            $category->update([
                'image_path' => CategoryImageStorage::store($category, $image),
            ]);
        }

        AiContentStatusManager::markPendingIfAiFieldsEmpty($category->refresh(), self::aiContentFieldKeys());

        return $category->refresh();
    }

    public static function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category has child categories and cannot be deleted.',
            ]);
        }

        CategoryImageStorage::deleteIfExists($category->image_path);
        $category->delete();
    }

    public static function approveContentReview(Category $category, User $reviewer): Category
    {
        $category->update([
            'content_reviewed_at' => now(),
            'content_reviewed_by' => $reviewer->id,
        ]);

        AiContentStatusManager::markComplete($category);

        return $category->refresh();
    }

    public static function duplicate(Category $source): Category
    {
        $nameEn = self::uniqueDuplicateName($source->name_en, $source->parent_id);

        $category = Category::query()->create([
            'parent_id' => $source->parent_id,
            'code' => CategoryCodeGenerator::next(),
            'name_en' => $nameEn,
            'name_ur' => filled($source->name_ur) ? $source->name_ur.' (کاپی)' : null,
            'hs_code' => $source->hs_code,
            'display_order' => $source->display_order,
            'is_active' => false,
            'short_description_en' => $source->short_description_en,
            'short_description_ur' => $source->short_description_ur,
            'long_description_en' => $source->long_description_en,
            'long_description_ur' => $source->long_description_ur,
            'usage_en' => $source->usage_en,
            'usage_ur' => $source->usage_ur,
            'benefits_en' => $source->benefits_en,
            'benefits_ur' => $source->benefits_ur,
            'warnings_en' => $source->warnings_en,
            'warnings_ur' => $source->warnings_ur,
            'seo_title' => $source->seo_title,
            'seo_focus_keyword_en' => $source->seo_focus_keyword_en,
            'seo_focus_keyword_ur' => $source->seo_focus_keyword_ur,
            'meta_description' => $source->meta_description,
            'meta_keywords' => $source->meta_keywords,
            'url_slug' => filled($source->url_slug) ? $source->url_slug.'-copy' : null,
            'canonical_url' => null,
            'meta_robots' => $source->meta_robots,
            'og_title' => $source->og_title,
            'og_description' => $source->og_description,
            'synonyms_en' => $source->synonyms_en,
            'synonyms_ur' => $source->synonyms_ur,
            'alternate_spellings' => $source->alternate_spellings,
            'search_aliases' => $source->search_aliases,
            'ai_prompt_override' => $source->ai_prompt_override,
            'internal_tags' => $source->internal_tags,
            'google_category' => $source->google_category,
            'facebook_category' => $source->facebook_category,
        ]);

        if (filled($source->image_path)) {
            $copiedPath = CategoryImageStorage::duplicateFrom($source, $category);

            if ($copiedPath) {
                $category->update(['image_path' => $copiedPath]);
            }
        }

        AiContentStatusManager::markNeedsReview($category->refresh());

        return $category->refresh();
    }

    protected static function uniqueDuplicateName(string $nameEn, ?int $parentId): string
    {
        $base = trim($nameEn).' (Copy)';
        $candidate = $base;
        $suffix = 2;

        while (Category::query()->where('parent_id', $parentId)->where('name_en', $candidate)->exists()) {
            $candidate = $base.' '.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * @return list<array{id: int|string, label: string, depth: int, path: list<array{name: string, color: string, image: string|null}>}>
     */
    public static function parentSelectOptions(?int $excludeCategoryId = null): array
    {
        $excludeIds = $excludeCategoryId
            ? array_merge([$excludeCategoryId], self::descendantIds($excludeCategoryId))
            : [];

        $options = [
            ['id' => '', 'label' => 'Root level (no parent)', 'depth' => 0, 'path' => []],
        ];

        foreach (self::rootCategories() as $root) {
            self::appendSelectOption($options, $root, 0, [], $excludeIds);
        }

        return $options;
    }

    /**
     * @return list<array{name: string, color: string, image: string|null}>
     */
    public static function breadcrumbPathFor(?int $parentId): array
    {
        if (! $parentId) {
            return [];
        }

        $parent = Category::query()->find($parentId);

        if (! $parent) {
            return [];
        }

        $ancestors = [];
        $current = $parent;

        while ($current) {
            array_unshift($ancestors, self::breadcrumbCrumb($current));
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Full hierarchy from root to the given category (inclusive).
     *
     * @return list<array{
     *     level: int,
     *     code: string,
     *     english_name: string,
     *     urdu_name: string|null,
     *     is_current: bool,
     * }>
     */
    public static function hierarchyChain(Category $category): array
    {
        $ancestors = [];
        $current = $category->parent;

        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }

        $chain = [...$ancestors, $category];
        $rows = [];

        foreach ($chain as $index => $node) {
            $rows[] = [
                'level' => $index + 1,
                'code' => $node->code,
                'english_name' => $node->name_en,
                'urdu_name' => $node->name_ur,
                'is_current' => $node->id === $category->id,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{english_name: string}>  $hierarchy
     */
    public static function hierarchyBreadcrumb(array $hierarchy): string
    {
        return collect($hierarchy)
            ->pluck('english_name')
            ->filter()
            ->implode(' › ');
    }

    /**
     * @return Collection<int, Category>
     */
    public static function tree(): Collection
    {
        return self::rootCategories();
    }

    /**
     * @return Collection<int, Category>
     */
    protected static function rootCategories(): Collection
    {
        return Category::query()
            ->with(self::nestedChildrenRelation(8))
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * @param  array<string, string>  $formFields
     */
    public static function applyAiGeneratedContent(Category $category, array $formFields, string $model): Category
    {
        $attributes = [];

        foreach ($formFields as $formKey => $value) {
            $column = CategoryAiContentSchema::databaseColumnForFormField($formKey);

            if ($column === null) {
                continue;
            }

            $attributes[$column] = filled($value) ? $value : null;
        }

        $category->update([
            ...$attributes,
            'last_ai_generated_at' => now(),
            'last_ai_model' => $model,
        ]);

        AiContentStatusManager::markComplete($category);

        return $category->refresh();
    }

    /**
     * @return list<string>
     */
    public static function aiContentFieldKeys(): array
    {
        return [
            'short_description_en',
            'short_description_ur',
            'long_description_en',
            'long_description_ur',
            'usage_en',
            'usage_ur',
            'benefits_en',
            'benefits_ur',
            'warnings_en',
            'warnings_ur',
            'seo_title',
            'seo_focus_keyword_en',
            'seo_focus_keyword_ur',
            'meta_description',
            'meta_keywords',
            'url_slug',
            'synonyms_en',
            'synonyms_ur',
            'alternate_spellings',
            'search_aliases',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function mappedAttributes(array $data): array
    {
        return [
            'parent_id' => filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null,
            'name_en' => $data['name_en'],
            'name_ur' => $data['name_ur'],
            'hs_code' => $data['hs_code'] ?? null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'short_description_en' => $data['short_description_en'] ?? null,
            'short_description_ur' => $data['short_description_ur'] ?? null,
            'long_description_en' => $data['long_description_en'] ?? null,
            'long_description_ur' => $data['long_description_ur'] ?? null,
            'usage_en' => $data['usage_en'] ?? null,
            'usage_ur' => $data['usage_ur'] ?? null,
            'benefits_en' => $data['benefits_en'] ?? null,
            'benefits_ur' => $data['benefits_ur'] ?? null,
            'warnings_en' => $data['warnings_en'] ?? null,
            'warnings_ur' => $data['warnings_ur'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_focus_keyword_en' => $data['seo_focus_keyword_en'] ?? null,
            'seo_focus_keyword_ur' => $data['seo_focus_keyword_ur'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'url_slug' => $data['url_slug'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'meta_robots' => $data['meta_robots'] ?? 'index, follow',
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'synonyms_en' => $data['synonyms_en'] ?? null,
            'synonyms_ur' => $data['synonyms_ur'] ?? null,
            'alternate_spellings' => $data['alternate_spellings'] ?? null,
            'search_aliases' => $data['search_aliases'] ?? null,
            'ai_prompt_override' => $data['ai_prompt_override'] ?? null,
            'internal_tags' => $data['internal_tags'] ?? null,
        ];
    }

    protected static function assertUniqueNameUnderParent(string $nameEn, ?int $parentId, ?int $exceptId = null): void
    {
        $query = Category::query()
            ->where('parent_id', $parentId)
            ->where('name_en', $nameEn);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'categoryForm.english_name' => 'A category with this English name already exists under the selected parent.',
            ]);
        }
    }

    protected static function assertValidParent(Category $category, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($parentId === $category->id) {
            throw ValidationException::withMessages([
                'categoryForm.parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        if (in_array($parentId, self::descendantIds($category->id), true)) {
            throw ValidationException::withMessages([
                'categoryForm.parent_id' => 'A category cannot be moved under one of its descendants.',
            ]);
        }
    }

    /**
     * @return list<int>
     */
    protected static function descendantIds(int $categoryId): array
    {
        $ids = [];
        $children = Category::query()->where('parent_id', $categoryId)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, self::descendantIds($childId));
        }

        return $ids;
    }

    /**
     * @param  list<array{id: int|string, label: string, depth: int, path: list<array{name: string, color: string, image: string|null}>}>  $options
     * @param  list<array{name: string, color: string, image: string|null}>  $path
     * @param  list<int>  $excludeIds
     */
    protected static function appendSelectOption(array &$options, Category $category, int $depth, array $path, array $excludeIds): void
    {
        if (in_array($category->id, $excludeIds, true)) {
            return;
        }

        $crumb = self::breadcrumbCrumb($category);
        $currentPath = [...$path, $crumb];

        $options[] = [
            'id' => (string) $category->id,
            'label' => $category->name_en,
            'depth' => $depth,
            'path' => $currentPath,
        ];

        foreach ($category->children as $child) {
            self::appendSelectOption($options, $child, $depth + 1, $currentPath, $excludeIds);
        }
    }

    /**
     * @return array{name: string, color: string, image: string|null}
     */
    protected static function breadcrumbCrumb(Category $category): array
    {
        return [
            'name' => $category->name_en,
            'color' => self::colorForCategory($category->id),
            'image' => CategoryImageStorage::url($category->image_path),
        ];
    }

    protected static function colorForCategory(int $categoryId): string
    {
        $palette = ['#83B735', '#3B82F6', '#F59E0B', '#10B981', '#EF4444', '#8B5CF6', '#EC4899'];

        return $palette[$categoryId % count($palette)];
    }

    protected static function nestedChildrenRelation(int $depth): array
    {
        if ($depth <= 0) {
            return [];
        }

        return ['children' => fn ($query) => $query
            ->orderBy('display_order')
            ->orderBy('name_en')
            ->with(self::nestedChildrenRelation($depth - 1)),
        ];
    }
}
