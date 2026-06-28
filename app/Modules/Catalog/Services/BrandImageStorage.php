<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog\Brand;
use App\Modules\Catalog\Support\BrandLogoSpec;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class BrandImageStorage
{
    public static function store(Brand $brand, UploadedFile $file): string
    {
        if (! BrandLogoSpec::isAllowedUpload($file)) {
            throw new \InvalidArgumentException(BrandLogoSpec::invalidTypeMessage());
        }

        self::deleteIfExists($brand->logo_path);

        return $file->store("catalog/brands/{$brand->id}", 'public');
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

    public static function duplicateFrom(Brand $source, Brand $target): ?string
    {
        if (! filled($source->logo_path) || ! Storage::disk('public')->exists($source->logo_path)) {
            return null;
        }

        $extension = pathinfo($source->logo_path, PATHINFO_EXTENSION);
        $filename = 'copy-'.now()->format('YmdHis').($extension ? '.'.$extension : '');
        $destination = "catalog/brands/{$target->id}/{$filename}";

        Storage::disk('public')->makeDirectory("catalog/brands/{$target->id}");
        Storage::disk('public')->copy($source->logo_path, $destination);

        return $destination;
    }
}
