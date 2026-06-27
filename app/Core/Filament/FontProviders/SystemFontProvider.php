<?php

namespace App\Core\Filament\FontProviders;

use Filament\FontProviders\Contracts\FontProvider;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Uses the OS-installed font (Arial) without loading external webfonts.
 */
class SystemFontProvider implements FontProvider
{
    public function getHtml(string $family, ?string $url = null): Htmlable
    {
        return new HtmlString('');
    }
}
