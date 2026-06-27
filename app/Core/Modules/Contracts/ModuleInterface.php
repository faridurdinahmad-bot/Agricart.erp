<?php

namespace App\Core\Modules\Contracts;

use Filament\Panel;

interface ModuleInterface
{
    public static function id(): string;

    public static function registerPanel(Panel $panel): void;

    /**
     * @return class-string<\Filament\Pages\Page>
     */
    public static function homePage(): string;
}
