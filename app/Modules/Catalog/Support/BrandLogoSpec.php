<?php

namespace App\Modules\Catalog\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/** Semantic alias for brand logos — uses {@see CatalogWebpImageSpec}. */
final class BrandLogoSpec
{
    public const MAX_KILOBYTES = CatalogWebpImageSpec::MAX_KILOBYTES;

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return CatalogWebpImageSpec::allowedExtensions();
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return CatalogWebpImageSpec::allowedMimeTypes();
    }

    public static function acceptAttribute(): string
    {
        return CatalogWebpImageSpec::acceptAttribute();
    }

    public static function uploadHint(): string
    {
        return CatalogWebpImageSpec::uploadHint();
    }

    public static function invalidTypeMessage(): string
    {
        return CatalogWebpImageSpec::invalidTypeMessage();
    }

    /**
     * @return list<File|string>
     */
    public static function validationRules(bool $required = false): array
    {
        return CatalogWebpImageSpec::validationRules($required);
    }

    public static function isAllowedExtension(?string $extension): bool
    {
        return CatalogWebpImageSpec::isAllowedExtension($extension);
    }

    public static function isAllowedMimeType(string $mimeType): bool
    {
        return CatalogWebpImageSpec::isAllowedMimeType($mimeType);
    }

    public static function isAllowedUpload(UploadedFile|TemporaryUploadedFile $file): bool
    {
        return CatalogWebpImageSpec::isAllowedUpload($file);
    }
}
