<?php

namespace App\Core\Modules;

use App\Core\Modules\Contracts\ModuleInterface;
use Filament\Clusters\Cluster;
use Filament\Pages\Page;
use Filament\Panel;

/**
 * Central registry for Agricart Filament sidebar modules.
 *
 * Modules are auto-discovered from app/Modules/{Name}/{Name}Module.php.
 *
 * To add a module:
 * 1. Copy stubs/module/ into app/Modules/{ModuleName}/
 * 2. Replace placeholders (see stubs/module/SETUP.md)
 * 3. Permissions sync automatically when the catalog changes (see PermissionGate::syncIfStale)
 *
 * To remove a module: delete its folder under app/Modules/.
 *
 * Do not edit existing modules when adding a new one.
 */
class ModuleRegistry
{
    protected static bool $discovered = false;

    /**
     * @var array<class-string<ModuleInterface>>
     */
    protected static array $modules = [];

    /**
     * Discover and register all modules under app/Modules/*.
     */
    public static function discover(): void
    {
        if (static::$discovered) {
            return;
        }

        $candidates = [];

        foreach (glob(app_path('Modules/*'), GLOB_ONLYDIR) ?: [] as $modulePath) {
            $name = basename($modulePath);
            $class = "App\\Modules\\{$name}\\{$name}Module";

            if (! class_exists($class)) {
                continue;
            }

            if (! is_subclass_of($class, ModuleInterface::class)) {
                continue;
            }

            $candidates[] = $class;
        }

        usort(
            $candidates,
            fn (string $a, string $b): int => [static::navigationSort($a), $a::id()]
                <=> [static::navigationSort($b), $b::id()],
        );

        foreach ($candidates as $module) {
            static::register($module);
        }

        static::$discovered = true;
    }

    /**
     * @param  class-string<ModuleInterface>  $module
     */
    public static function register(string $module): void
    {
        if (! in_array($module, static::$modules, true)) {
            static::$modules[] = $module;
        }
    }

    /**
     * @return array<class-string<ModuleInterface>>
     */
    public static function all(): array
    {
        static::discover();

        return static::$modules;
    }

    public static function configurePanel(Panel $panel): void
    {
        foreach (static::all() as $module) {
            $module::registerPanel($panel);
        }
    }

    public static function homeUrl(): ?string
    {
        $module = static::all()[0] ?? null;

        if ($module === null) {
            return null;
        }

        return $module::homePage()::getUrl();
    }

    /**
     * @param  class-string<ModuleInterface>  $module
     */
    protected static function navigationSort(string $module): int
    {
        $homePage = $module::homePage();

        if (! is_subclass_of($homePage, Page::class)) {
            return PHP_INT_MAX;
        }

        /** @var class-string<Page> $homePage */
        $clusterClass = $homePage::getCluster();

        if (! $clusterClass || ! is_subclass_of($clusterClass, Cluster::class)) {
            return PHP_INT_MAX;
        }

        /** @var class-string<Cluster> $clusterClass */
        return $clusterClass::getNavigationSort() ?? PHP_INT_MAX;
    }
}
