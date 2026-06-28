<?php

namespace App\Core\ContentAudit\Services;

use App\Core\ContentAudit\Contracts\ContentAuditDocumentBuilder;
use App\Core\ContentAudit\Enums\ContentAuditFormat;
use App\Core\ContentAudit\Support\ContentAuditJsonRenderer;
use App\Core\ContentAudit\Support\ContentAuditMarkdownRenderer;
use App\Core\ContentAudit\Support\ContentAuditPdfRenderer;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ContentAuditExportService
{
    public function __construct(
        private readonly ContentAuditMarkdownRenderer $markdownRenderer,
        private readonly ContentAuditJsonRenderer $jsonRenderer,
        private readonly ContentAuditPdfRenderer $pdfRenderer,
    ) {}

    /**
     * @param  array<string, mixed>  $document
     */
    public function render(array $document, ContentAuditFormat $format): string
    {
        return match ($format) {
            ContentAuditFormat::Markdown => $this->markdownRenderer->render($document),
            ContentAuditFormat::Json => $this->jsonRenderer->render($document),
            ContentAuditFormat::Pdf => $this->pdfRenderer->render($document),
        };
    }

    public function downloadFromBuilder(
        ContentAuditDocumentBuilder $builder,
        object $record,
        ContentAuditFormat $format,
    ): StreamedResponse {
        return $this->download($builder->build($record), $format);
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public function download(array $document, ContentAuditFormat $format): StreamedResponse
    {
        $filename = $this->filename($document, $format);
        $content = $this->render($document, $format);

        return response()->streamDownload(
            function () use ($content): void {
                echo $content;
            },
            $filename,
            [
                'Content-Type' => $format->mimeType(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public function filename(array $document, ContentAuditFormat $format): string
    {
        $meta = $document['meta'] ?? [];
        $entity = Str::slug((string) ($meta['entity'] ?? 'record'));
        $code = Str::slug((string) ($meta['entity_code'] ?? 'export'));
        $date = now()->format('Y-m-d');

        return "{$entity}-{$code}-review-{$date}.{$format->extension()}";
    }
}
