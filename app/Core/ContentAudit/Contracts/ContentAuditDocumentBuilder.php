<?php

namespace App\Core\ContentAudit\Contracts;

interface ContentAuditDocumentBuilder
{
    public function module(): string;

    public function entity(): string;

    /**
     * @return array<string, mixed>
     */
    public function build(object $record): array;
}
