<?php

namespace App\Core\ContentAudit\Support;

final class ContentAuditPdfLayout
{
    public static function summaryValue(array $document, string $label): ?string
    {
        $field = collect($document['summary'] ?? [])->firstWhere('label', $label);

        if (! filled($field['value'] ?? null)) {
            return null;
        }

        return (string) $field['value'];
    }

    /**
     * @return array{metric: string, value: string, status: string}|null
     */
    public static function qualityMetric(array $analysis, string $metric): ?array
    {
        $row = collect($analysis['content_quality'] ?? [])->firstWhere('metric', $metric);

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function section(array $document, string $title): ?array
    {
        $section = collect($document['sections'] ?? [])->firstWhere('title', $title);

        return is_array($section) ? $section : null;
    }

    /**
     * @return list<array{label: string, value: string|null}>
     */
    public static function sectionFields(array $document, string $title): array
    {
        $section = self::section($document, $title);

        if (! $section) {
            return [];
        }

        $fields = $section['fields'] ?? [];

        foreach ($section['groups'] ?? [] as $group) {
            $fields = array_merge($fields, $group['fields'] ?? []);
        }

        return $fields;
    }

    public static function compact(?string $text, int $limit = 320): ?string
    {
        if (! filled($text)) {
            return null;
        }

        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 1).'…';
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'good' => 'badge badge--good',
            'warn' => 'badge badge--warn',
            'bad' => 'badge badge--bad',
            default => 'badge badge--neutral',
        };
    }

    public static function severityClass(string $severity): string
    {
        return match ($severity) {
            'critical' => 'severity severity--critical',
            'recommended' => 'severity severity--recommended',
            default => 'severity',
        };
    }
}
