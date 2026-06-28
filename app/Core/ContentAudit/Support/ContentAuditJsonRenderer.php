<?php

namespace App\Core\ContentAudit\Support;

final class ContentAuditJsonRenderer
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function render(array $document): string
    {
        return json_encode(
            $this->sanitize($document),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function sanitize(array $document): array
    {
        $sanitized = $document;

        if (isset($sanitized['image']) && is_array($sanitized['image'])) {
            unset($sanitized['image']['base64_data_uri']);
        }

        return $sanitized;
    }
}
