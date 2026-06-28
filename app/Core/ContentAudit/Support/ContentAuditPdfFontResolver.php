<?php

namespace App\Core\ContentAudit\Support;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

final class ContentAuditPdfFontResolver
{
    /**
     * @return array{
     *     fontDir: list<string>,
     *     fontdata: array<string, mixed>,
     *     sans_fonts: list<string>,
     *     default_font: string,
     * }
     */
    public static function configuration(): array
    {
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontConfig = (new FontVariables)->getDefaults();

        $fontDirs = array_merge(
            $defaultConfig['fontDir'],
            [resource_path('fonts/pdf')],
        );

        $fontData = $fontConfig['fontdata'];

        [$arial, $extraFontDirs] = self::arialFontDefinition();
        $fontData['arial'] = $arial;
        // Noto Naskh OTL tables exceed mPDF 8.3 MarkGlyphSets support; embedding without OTL
        // still renders full Urdu glyph coverage via autoArabic + explicit .pdf-urdu spans.
        $fontData['notonaskharabic'] = [
            'R' => 'NotoNaskhArabic-Regular.ttf',
            'B' => 'NotoNaskhArabic-Bold.ttf',
            'useOTL' => 0x00,
        ];
        $fontDirs = array_merge($fontDirs, $extraFontDirs);

        $sansFonts = $fontConfig['sans_fonts'];
        array_unshift($sansFonts, 'arial', 'notonaskharabic');

        return [
            'fontDir' => array_values(array_unique($fontDirs)),
            'fontdata' => $fontData,
            'sans_fonts' => $sansFonts,
            'default_font' => 'arial',
        ];
    }

    /**
     * @return array{0: array{R: string, B: string, I: string, BI: string}, 1: list<string>}
     */
    protected static function arialFontDefinition(): array
    {
        $windowsFontDir = 'C:\Windows\Fonts';
        $windowsRegular = $windowsFontDir.DIRECTORY_SEPARATOR.'arial.ttf';
        $windowsBold = $windowsFontDir.DIRECTORY_SEPARATOR.'arialbd.ttf';

        if (is_readable($windowsRegular) && is_readable($windowsBold)) {
            return [
                [
                    'R' => 'arial.ttf',
                    'B' => 'arialbd.ttf',
                    'I' => 'arial.ttf',
                    'BI' => 'arialbd.ttf',
                ],
                [$windowsFontDir],
            ];
        }

        return [
            [
                'R' => 'Arimo-Regular.ttf',
                'B' => 'Arimo-Bold.ttf',
                'I' => 'Arimo-Regular.ttf',
                'BI' => 'Arimo-Bold.ttf',
            ],
            [],
        ];
    }
}
