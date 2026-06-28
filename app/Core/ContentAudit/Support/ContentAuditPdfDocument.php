<?php

namespace App\Core\ContentAudit\Support;

final class ContentAuditPdfDocument
{
    public function __construct(
        private readonly string $binary,
        private readonly int $pageCount,
    ) {}

    public function output(): string
    {
        return $this->binary;
    }

    public function pageCount(): int
    {
        return $this->pageCount;
    }
}
