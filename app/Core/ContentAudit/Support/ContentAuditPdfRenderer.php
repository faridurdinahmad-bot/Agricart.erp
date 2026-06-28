<?php

namespace App\Core\ContentAudit\Support;

use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

final class ContentAuditPdfRenderer
{
    /**
     * @param  array<string, mixed>  $document
     */
    public function render(array $document, string $view = 'content-audit.category-review-pdf'): string
    {
        return $this->renderDocument($document, $view)->output();
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public function pageCount(array $document, string $view = 'content-audit.category-review-pdf'): int
    {
        return $this->renderDocument($document, $view)->pageCount();
    }

    /**
     * @param  array<string, mixed>  $document
     */
    protected function renderDocument(array $document, string $view): ContentAuditPdfDocument
    {
        $html = View::make($view, ['document' => $document])->render();
        $fonts = ContentAuditPdfFontResolver::configuration();

        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 12,
            'tempDir' => $tempDir,
            'fontDir' => $fonts['fontDir'],
            'fontdata' => $fonts['fontdata'],
            'sans_fonts' => $fonts['sans_fonts'],
            'default_font' => $fonts['default_font'],
            'languageToFont' => new ContentAuditLanguageToFont,
            'shrink_tables_to_fit' => 1,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->autoArabic = true;
        $mpdf->useSubstitutions = false;
        $mpdf->SetDirectionality('ltr');
        $mpdf->WriteHTML($html);

        return new ContentAuditPdfDocument($mpdf->Output('', 'S'), $mpdf->page);
    }
}
