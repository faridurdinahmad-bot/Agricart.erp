<?php

namespace App\Modules\Catalog\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Canonical catalog image rules for Agricart ERP.
 * All catalog uploads accept WebP only.
 */
final class CatalogWebpImageSpec
{
    public const MAX_KILOBYTES = 5120;

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return ['webp'];
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return [
            'image/webp',
        ];
    }

    public static function acceptAttribute(): string
    {
        return '.webp,image/webp';
    }

    public static function uploadHint(): string
    {
        return sprintf(
            'WebP only. Max %d MB.',
            (int) (self::MAX_KILOBYTES / 1024),
        );
    }

    public static function invalidTypeMessage(): string
    {
        return 'Only WebP images are allowed.';
    }

    /**
     * @return list<File|string>
     */
    public static function validationRules(bool $required = false): array
    {
        $fileRule = File::types(self::allowedExtensions())
            ->max(self::MAX_KILOBYTES);

        return [
            $required ? 'required' : 'nullable',
            $fileRule,
        ];
    }

    public static function isAllowedExtension(?string $extension): bool
    {
        if (! is_string($extension) || $extension === '') {
            return false;
        }

        return in_array(strtolower($extension), self::allowedExtensions(), true);
    }

    public static function isAllowedMimeType(string $mimeType): bool
    {
        if ($mimeType === '') {
            return false;
        }

        return in_array(strtolower($mimeType), self::allowedMimeTypes(), true);
    }

    public static function isAllowedUpload(UploadedFile|TemporaryUploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (! self::isAllowedExtension($extension)) {
            return false;
        }

        if ($mimeType === '') {
            return true;
        }

        return self::isAllowedMimeType($mimeType);
    }
}
