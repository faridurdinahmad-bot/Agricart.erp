<?php

namespace App\Modules\Catalog\Services;

use App\Core\Ai\Services\AiContentStatusManager;
use App\Models\Catalog\Brand;
use App\Models\Catalog\BrandDeletionRequest;
use App\Models\User;
use App\Modules\Catalog\Support\BrandAiContentSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

final class BrandManager
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $categoryIds
     */
    public static function create(array $data, array $categoryIds = [], ?UploadedFile $logo = null): Brand
    {
        self::assertUniqueName($data['name_en']);

        $brand = Brand::query()->create([
            ...self::mappedAttributes($data),
            'code' => BrandCodeGenerator::next(),
        ]);

        if ($logo) {
            $brand->update([
                'logo_path' => BrandImageStorage::store($brand, $logo),
            ]);
        }

        self::syncCategories($brand, $categoryIds);

        AiContentStatusManager::markPendingIfAiFieldsEmpty($brand->refresh(), self::aiContentFieldKeys());

        return $brand->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $categoryIds
     */
    public static function update(Brand $brand, array $data, array $categoryIds = [], ?UploadedFile $logo = null): Brand
    {
        self::assertUniqueName($data['name_en'], $brand->id);

        $brand->update(self::mappedAttributes($data));

        if ($logo) {
            $brand->update([
                'logo_path' => BrandImageStorage::store($brand, $logo),
            ]);
        }

        self::syncCategories($brand, $categoryIds);

        AiContentStatusManager::markPendingIfAiFieldsEmpty($brand->refresh(), self::aiContentFieldKeys());

        return $brand->refresh();
    }

    public static function requestDeletion(Brand $brand, User $requestedBy, ?string $reason = null): BrandDeletionRequest
    {
        return BrandDeletionService::requestDeletion($brand, $requestedBy, $reason);
    }

    public static function delete(Brand $brand): void
    {
        throw ValidationException::withMessages([
            'brand' => 'Direct brand deletion is not allowed. Submit a deletion request for approval instead.',
        ]);
    }

    public static function approveContentReview(Brand $brand, User $reviewer): Brand
    {
        $brand->update([
            'content_reviewed_at' => now(),
            'content_reviewed_by' => $reviewer->id,
        ]);

        AiContentStatusManager::markComplete($brand);

        return $brand->refresh();
    }

    public static function duplicate(Brand $source): Brand
    {
        $source->loadMissing('categories');
        $nameEn = self::uniqueDuplicateName($source->name_en);

        $brand = Brand::query()->create([
            'code' => BrandCodeGenerator::next(),
            'name_en' => $nameEn,
            'name_ur' => filled($source->name_ur) ? $source->name_ur.' (کاپی)' : '',
            'short_note' => $source->short_note,
            'short_description_en' => $source->short_description_en,
            'short_description_ur' => $source->short_description_ur,
            'long_description_en' => $source->long_description_en,
            'long_description_ur' => $source->long_description_ur,
            'brand_overview_en' => $source->brand_overview_en,
            'brand_overview_ur' => $source->brand_overview_ur,
            'seo_title' => $source->seo_title,
            'seo_description' => $source->seo_description,
            'seo_keywords' => $source->seo_keywords,
            'country' => $source->country,
            'website' => $source->website,
            'is_active' => false,
        ]);

        if (filled($source->logo_path)) {
            $copiedPath = BrandImageStorage::duplicateFrom($source, $brand);

            if ($copiedPath) {
                $brand->update(['logo_path' => $copiedPath]);
            }
        }

        $brand->categories()->sync($source->categories->pluck('id')->all());

        AiContentStatusManager::markNeedsReview($brand->refresh());

        return $brand->refresh();
    }

    /**
     * @param  array<string, string>  $formFields
     */
    public static function applyAiGeneratedContent(Brand $brand, array $formFields, string $model): Brand
    {
        $attributes = [];

        foreach ($formFields as $formKey => $value) {
            $column = BrandAiContentSchema::databaseColumnForFormField($formKey);

            if ($column === null) {
                continue;
            }

            $attributes[$column] = filled($value) ? $value : null;
        }

        $brand->update([
            ...$attributes,
            'last_ai_generated_at' => now(),
            'last_ai_model' => $model,
        ]);

        AiContentStatusManager::markComplete($brand);

        return $brand->refresh();
    }

    /**
     * @return list<array{id: int|string, label: string, depth: int, path: list<array{name: string, color: string, image: string|null}>}>
     */
    public static function categorySelectOptions(): array
    {
        return array_values(array_filter(
            CategoryManager::parentSelectOptions(),
            fn (array $option): bool => $option['id'] !== '',
        ));
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
            'brand_overview_en',
            'brand_overview_ur',
            'seo_title',
            'seo_description',
            'seo_keywords',
        ];
    }

    /**
     * @param  list<int>  $categoryIds
     */
    protected static function syncCategories(Brand $brand, array $categoryIds): void
    {
        $brand->categories()->sync(array_values(array_unique(array_filter($categoryIds))));
    }

    protected static function uniqueDuplicateName(string $nameEn): string
    {
        $base = trim($nameEn).' (Copy)';
        $candidate = $base;
        $suffix = 2;

        while (Brand::query()->where('name_en', $candidate)->exists()) {
            $candidate = $base.' '.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    protected static function assertUniqueName(string $nameEn, ?int $ignoreId = null): void
    {
        $query = Brand::query()->where('name_en', trim($nameEn));

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'brandForm.english_name' => 'A brand with this English name already exists.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function mappedAttributes(array $data): array
    {
        return [
            'name_en' => $data['name_en'],
            'name_ur' => $data['name_ur'],
            'short_note' => $data['short_note'] ?? null,
            'short_description_en' => $data['short_description_en'] ?? null,
            'short_description_ur' => $data['short_description_ur'] ?? null,
            'long_description_en' => $data['long_description_en'] ?? null,
            'long_description_ur' => $data['long_description_ur'] ?? null,
            'brand_overview_en' => $data['brand_overview_en'] ?? null,
            'brand_overview_ur' => $data['brand_overview_ur'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'seo_keywords' => $data['seo_keywords'] ?? null,
            'country' => $data['country'] ?? null,
            'website' => $data['website'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }
}
