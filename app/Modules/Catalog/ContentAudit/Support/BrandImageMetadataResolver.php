<?php

namespace App\Modules\Catalog\ContentAudit\Support;

final class BrandImageMetadataResolver
{
    /**
     * @return array<string, mixed>
     */
    public static function resolve(?string $path): array
    {
        return CategoryImageMetadataResolver::resolve($path);
    }

    public static function base64DataUri(?string $path): ?string
    {
        return CategoryImageMetadataResolver::base64DataUri($path);
    }
}
