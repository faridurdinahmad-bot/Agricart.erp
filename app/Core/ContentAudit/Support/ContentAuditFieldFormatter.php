<?php

namespace App\Core\ContentAudit\Support;

final class ContentAuditFieldFormatter
{
    public static function text(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    public static function boolean(?bool $value): string
    {
        return $value ? 'Active' : 'Inactive';
    }

    /**
     * @return array{label: string, value: string|null}
     */
    public static function field(string $label, mixed $value): array
    {
        if (is_bool($value)) {
            return ['label' => $label, 'value' => self::boolean($value)];
        }

        if ($value === null || $value === '') {
            return ['label' => $label, 'value' => null];
        }

        return ['label' => $label, 'value' => trim((string) $value)];
    }
}
