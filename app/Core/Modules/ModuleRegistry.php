<?php

namespace App\Core\Modules;

use App\Core\Modules\Contracts\ModuleInterface;
use Filament\Panel;

/**
 * Central registry for Agricart Filament sidebar modules.
 *
 * To add a new module (Products, Inventory, POS, etc.):
 * 1. Copy stubs/module/ into app/Modules/{ModuleName}/
 * 2. Replace placeholders (see stubs/module/SETUP.md)
 * 3. Register once: ModuleRegistry::register(YourModule::class);
 *
 * Do not edit existing modules when adding a new one.
 */
class ModuleRegistry
{
    /**
     * @var array<class-string<ModuleInterface>>
     */
    protected static array $modules = [];

    /**
     * @param  class-string<ModuleInterface>  $module
     */
    public static function register(string $module): void
    {
        static::$modules[] = $module;
    }

    /**
     * @return array<class-string<ModuleInterface>>
     */
    public static function all(): array
    {
        return static::$modules;
    }

    public static function configurePanel(Panel $panel): void
    {
        foreach (static::$modules as $module) {
            $module::registerPanel($panel);
        }
    }

    public static function homeUrl(): ?string
    {
        $module = static::$modules[0] ?? null;

        if ($module === null) {
            return null;
        }

        return $module::homePage()::getUrl();
    }
}
