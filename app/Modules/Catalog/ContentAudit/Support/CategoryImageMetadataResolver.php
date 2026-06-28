<?php

namespace App\Modules\Catalog\ContentAudit\Support;

use Illuminate\Support\Facades\Storage;

final class CategoryImageMetadataResolver
{
    /**
     * @return array{
     *     path: string|null,
     *     filename: string|null,
     *     url: string|null,
     *     format: string|null,
     *     size_bytes: int|null,
     *     size_human: string|null,
     *     width: int|null,
     *     height: int|null,
     *     mime_type: string|null,
     * }
     */
    public static function resolve(?string $path): array
    {
        if (! filled($path)) {
            return self::empty();
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return [
                ...self::empty(),
                'path' => $path,
                'filename' => basename($path),
            ];
        }

        $absolutePath = $disk->path($path);
        $sizeBytes = $disk->size($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => null,
        };

        $width = null;
        $height = null;

        if (is_file($absolutePath) && $extension !== 'svg') {
            $dimensions = @getimagesize($absolutePath);

            if (is_array($dimensions)) {
                $width = $dimensions[0] ?? null;
                $height = $dimensions[1] ?? null;
                $mimeType ??= $dimensions['mime'] ?? null;
            }
        }

        return [
            'path' => $path,
            'filename' => basename($path),
            'url' => $disk->url($path),
            'format' => $extension !== '' ? $extension : null,
            'size_bytes' => $sizeBytes,
            'size_human' => self::humanFileSize($sizeBytes),
            'width' => $width,
            'height' => $height,
            'mime_type' => $mimeType,
        ];
    }

    public static function base64DataUri(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);
        $meta = self::resolve($path);
        $mime = $meta['mime_type'] ?? 'application/octet-stream';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    /**
     * @return array<string, null>
     */
    protected static function empty(): array
    {
        return [
            'path' => null,
            'filename' => null,
            'url' => null,
            'format' => null,
            'size_bytes' => null,
            'size_human' => null,
            'width' => null,
            'height' => null,
            'mime_type' => null,
        ];
    }

    protected static function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 2).' MB';
    }
}
