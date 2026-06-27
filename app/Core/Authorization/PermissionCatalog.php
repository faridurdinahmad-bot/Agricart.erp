<?php

namespace App\Core\Authorization;

use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Filament\Pages\BaseModulePage;
use App\Core\Modules\ModuleNavigationSort;
use Filament\Clusters\Cluster;
use Illuminate\Support\Str;
use ReflectionClass;

final class PermissionCatalog
{
    public const DASHBOARD_MODULE = 'dashboard';

    public const DASHBOARD_PAGE = 'home';

    public const SUPER_ADMIN_SLUG = 'super-admin';

    /**
     * @return list<array{
     *     module: string,
     *     module_label: string,
     *     module_sort: int,
     *     page: string,
     *     page_label: string,
     *     page_sort: int
     * }>
     */
    public static function entries(): array
    {
        $entries = [
            [
                'module' => self::DASHBOARD_MODULE,
                'module_label' => 'Dashboard',
                'module_sort' => ModuleNavigationSort::DASHBOARD,
                'page' => self::DASHBOARD_PAGE,
                'page_label' => 'Dashboard',
                'page_sort' => 1,
            ],
        ];

        foreach (glob(app_path('Modules/*/Pages/*.php')) as $path) {
            $normalizedPath = str_replace('\\', '/', $path);
            $modulesRoot = str_replace('\\', '/', app_path('Modules')).'/';

            if (! str_starts_with($normalizedPath, $modulesRoot)) {
                continue;
            }

            $relative = substr($normalizedPath, strlen($modulesRoot));
            $segments = explode('/', $relative);

            if (count($segments) < 3) {
                continue;
            }

            $class = 'App\\Modules\\'.$segments[0].'\\Pages\\'.pathinfo($segments[2], PATHINFO_FILENAME);

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || ! $reflection->isSubclassOf(BaseModulePage::class)) {
                continue;
            }

            /** @var class-string<BaseModulePage> $class */
            $clusterClass = $class::getCluster();

            if (! $clusterClass || ! is_subclass_of($clusterClass, Cluster::class)) {
                continue;
            }

            $entries[] = [
                'module' => $clusterClass::getSlug(),
                'module_label' => $clusterClass::getNavigationLabel(),
                'module_sort' => $clusterClass::getNavigationSort() ?? ModuleNavigationSort::NEXT_AVAILABLE,
                'page' => $class::getSlug(),
                'page_label' => $class::getNavigationLabel(),
                'page_sort' => $class::getNavigationSort() ?? 999,
            ];
        }

        usort($entries, function (array $a, array $b): int {
            return [$a['module_sort'], $a['page_sort'], $a['page_label']]
                <=> [$b['module_sort'], $b['page_sort'], $b['page_label']];
        });

        return $entries;
    }

    /**
     * @return list<array{module: string, module_label: string, pages: list<array{page: string, page_label: string, actions: list<PermissionAction>}>}>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::entries() as $entry) {
            if (! isset($groups[$entry['module']])) {
                $groups[$entry['module']] = [
                    'module' => $entry['module'],
                    'module_label' => $entry['module_label'],
                    'pages' => [],
                ];
            }

            $groups[$entry['module']]['pages'][] = [
                'page' => $entry['page'],
                'page_label' => $entry['page_label'],
                'actions' => PermissionAction::all(),
            ];
        }

        return array_values($groups);
    }

    public static function key(string $module, string $page, PermissionAction $action): string
    {
        return "{$module}.{$page}.{$action->value}";
    }

    public static function parseKey(string $key): ?array
    {
        $parts = explode('.', $key);

        if (count($parts) !== 3) {
            return null;
        }

        $action = PermissionAction::tryFrom($parts[2]);

        if (! $action) {
            return null;
        }

        return [
            'module' => $parts[0],
            'page' => $parts[1],
            'action' => $action,
        ];
    }

    public static function dashboardViewKey(): string
    {
        return self::key(self::DASHBOARD_MODULE, self::DASHBOARD_PAGE, PermissionAction::View);
    }

    public static function pageViewKey(string $module, string $page): string
    {
        return self::key($module, $page, PermissionAction::View);
    }

    /**
     * @return list<string>
     */
    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::entries() as $entry) {
            foreach (PermissionAction::all() as $action) {
                $keys[] = self::key($entry['module'], $entry['page'], $action);
            }
        }

        return $keys;
    }

    /**
     * Legacy module slug derived from the module folder name (e.g. HR -> h-r).
     */
    public static function legacyModuleSlug(string $moduleFolder): string
    {
        return Str::kebab($moduleFolder);
    }

    /**
     * Legacy page slug derived from the page class basename (e.g. OverviewPage -> overview-page).
     */
    public static function legacyPageSlug(string $pageClassBasename): string
    {
        return (string) str($pageClassBasename)->kebab()->slug();
    }
}
