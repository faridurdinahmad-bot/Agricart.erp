<?php

namespace App\Core\ContentAudit\Support;

use Mpdf\Language\LanguageToFont;

final class ContentAuditLanguageToFont extends LanguageToFont
{
    public function getLanguageOptions($llcc, $adobeCJK)
    {
        $tags = explode('-', (string) $llcc);
        $lang = strtolower($tags[0]);
        $script = '';

        if (! empty($tags[1]) && strlen($tags[1]) === 4) {
            $script = strtolower($tags[1]);
        }

        if ($this->usesArabicScriptFont($lang, $script)) {
            return [false, 'notonaskharabic'];
        }

        return parent::getLanguageOptions($llcc, $adobeCJK);
    }

    protected function usesArabicScriptFont(string $lang, string $script): bool
    {
        if (in_array($lang, ['ur', 'urd', 'ar', 'fa', 'fas', 'ps', 'pus', 'ku', 'kur', 'sd', 'snd'], true)) {
            return true;
        }

        return $lang === 'und' && $script === 'arab';
    }
}
