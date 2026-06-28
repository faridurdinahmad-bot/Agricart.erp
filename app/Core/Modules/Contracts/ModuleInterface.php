<?php

namespace App\Core\Modules\Contracts;

use Filament\Pages\Page;
use Filament\Panel;

interface ModuleInterface
{
    public static function id(): string;

    public static function registerPanel(Panel $panel): void;

    /**
     * @return class-string<Page>
     */
    public static function homePage(): string;
}
