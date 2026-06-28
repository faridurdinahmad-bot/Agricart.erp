<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog\Category;
use App\Modules\Catalog\Support\CatalogWebpImageSpec;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class CategoryImageStorage
{
    public static function store(Category $category, UploadedFile $file): string
    {
        if (! CatalogWebpImageSpec::isAllowedUpload($file)) {
            throw new \InvalidArgumentException(CatalogWebpImageSpec::invalidTypeMessage());
        }

        self::deleteIfExists($category->image_path);

        return $file->store("catalog/categories/{$category->id}", 'public');
    }

    public static function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $path);

        if (! Storage::disk('public')->exists($normalizedPath)) {
            return null;
        }

        return '/storage/'.$normalizedPath;
    }

    public static function duplicateFrom(Category $source, Category $target): ?string
    {
        if (! filled($source->image_path) || ! Storage::disk('public')->exists($source->image_path)) {
            return null;
        }

        $extension = pathinfo($source->image_path, PATHINFO_EXTENSION);
        $filename = 'copy-'.now()->format('YmdHis').($extension ? '.'.$extension : '');
        $destination = "catalog/categories/{$target->id}/{$filename}";

        Storage::disk('public')->makeDirectory("catalog/categories/{$target->id}");
        Storage::disk('public')->copy($source->image_path, $destination);

        return $destination;
    }
}
