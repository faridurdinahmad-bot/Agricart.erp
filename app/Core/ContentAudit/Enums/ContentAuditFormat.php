<?php

namespace App\Core\ContentAudit\Enums;

enum ContentAuditFormat: string
{
    case Markdown = 'markdown';
    case Pdf = 'pdf';
    case Json = 'json';

    public function label(): string
    {
        return match ($this) {
            self::Markdown => 'Markdown (.md)',
            self::Pdf => 'PDF (.pdf)',
            self::Json => 'JSON (.json)',
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::Markdown => 'md',
            self::Pdf => 'pdf',
            self::Json => 'json',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::Markdown => 'text/markdown; charset=UTF-8',
            self::Pdf => 'application/pdf',
            self::Json => 'application/json; charset=UTF-8',
        };
    }

    public static function tryFromInput(string $format): ?self
    {
        return match (strtolower(trim($format))) {
            'markdown', 'md' => self::Markdown,
            'pdf' => self::Pdf,
            'json' => self::Json,
            default => self::tryFrom($format),
        };
    }
}
