<?php

namespace App\Core\Authorization;

use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Clusters\Cluster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

final class PermissionGate
{
    public static function forUser(?User $user): self
    {
        return new self($user);
    }

    public function __construct(private readonly ?User $user) {}

    public function allows(string $key): bool
    {
        if (! $this->user || ! $this->user->isActive()) {
            return false;
        }

        if ($this->user->isSuperAdmin()) {
            return true;
        }

        return in_array($key, $this->keys(), true);
    }

    public function canViewModule(string $module): bool
    {
        if ($this->user?->isSuperAdmin()) {
            return true;
        }

        foreach ($this->keys() as $key) {
            if (str_starts_with($key, "{$module}.")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Always resolved from the database so permission changes take effect immediately.
     *
     * @return list<string>
     */
    public function keys(): array
    {
        if (! $this->user) {
            return [];
        }

        if ($this->user->isSuperAdmin()) {
            return PermissionCatalog::allKeys();
        }

        if (! $this->user->role_id) {
            return [];
        }

        return Permission::query()
            ->whereHas('roles', fn ($query) => $query
                ->where('roles.id', $this->user->role_id)
                ->where('is_active', true))
            ->pluck('key')
            ->all();
    }

    /**
     * Clear any in-memory auth relationships after permission changes.
     */
    public static function flush(?User $user = null): void
    {
        if ($user) {
            $user->unsetRelation('role');

            return;
        }
    }

    public static function flushForRole(Role $role): void
    {
        $role->unsetRelation('permissions');

        User::query()
            ->where('role_id', $role->id)
            ->each(fn (User $user) => self::flush($user));

        if ($current = auth()->user()) {
            if ($current->role_id === $role->id) {
                self::flush($current);
            }
        }
    }

    public static function flushAll(): void
    {
        User::query()
            ->whereNotNull('role_id')
            ->each(fn (User $user) => self::flush($user));

        if ($current = auth()->user()) {
            self::flush($current);
        }
    }

    public static function syncDefinitions(): void
    {
        foreach (PermissionCatalog::entries() as $entry) {
            foreach (PermissionAction::all() as $action) {
                Permission::query()->updateOrCreate(
                    ['key' => PermissionCatalog::key($entry['module'], $entry['page'], $action)],
                    [
                        'module' => $entry['module'],
                        'page' => $entry['page'],
                        'action' => $action->value,
                    ],
                );
            }
        }

        self::migrateLegacyPermissionKeys();
    }

    /**
     * Move role assignments from legacy permission keys to canonical module/page slugs.
     */
    protected static function migrateLegacyPermissionKeys(): void
    {
        $modulesRoot = str_replace('\\', '/', app_path('Modules')).'/';

        foreach (glob(app_path('Modules/*/Pages/*.php')) as $path) {
            $normalizedPath = str_replace('\\', '/', $path);

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

            $legacyModule = PermissionCatalog::legacyModuleSlug($segments[0]);
            $canonicalModule = $clusterClass::getSlug();
            $legacyPage = PermissionCatalog::legacyPageSlug(pathinfo($segments[2], PATHINFO_FILENAME));
            $canonicalPage = $class::getSlug();

            $moduleSlugs = array_values(array_unique([$legacyModule, $canonicalModule]));
            $pageSlugs = array_values(array_unique([$legacyPage, $canonicalPage]));

            foreach ($moduleSlugs as $moduleSlug) {
                foreach ($pageSlugs as $pageSlug) {
                    foreach (PermissionAction::all() as $action) {
                        self::migratePermissionKey(
                            PermissionCatalog::key($moduleSlug, $pageSlug, $action),
                            PermissionCatalog::key($canonicalModule, $canonicalPage, $action),
                        );
                    }
                }
            }
        }

        self::purgeOrphanPermissions();
    }

    protected static function purgeOrphanPermissions(): void
    {
        $validKeys = PermissionCatalog::allKeys();

        Permission::query()
            ->whereNotIn('key', $validKeys)
            ->each(function (Permission $permission): void {
                DB::table('permission_role')->where('permission_id', $permission->id)->delete();
                $permission->delete();
            });
    }

    protected static function migratePermissionKey(string $legacyKey, string $canonicalKey): void
    {
        if ($legacyKey === $canonicalKey) {
            return;
        }

        $legacy = Permission::query()->where('key', $legacyKey)->first();
        $canonical = Permission::query()->where('key', $canonicalKey)->first();

        if (! $legacy) {
            return;
        }

        if (! $canonical) {
            $legacy->update(['key' => $canonicalKey]);

            return;
        }

        if ($legacy->id === $canonical->id) {
            return;
        }

        $roleIds = DB::table('permission_role')
            ->where('permission_id', $legacy->id)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'permission_id' => $canonical->id,
                    'role_id' => $roleId,
                ],
                [],
            );
        }

        DB::table('permission_role')->where('permission_id', $legacy->id)->delete();
        $legacy->delete();
    }

    /**
     * @return Collection<int, Permission>
     */
    public static function allPermissionsGrouped(): Collection
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('page')
            ->orderBy('action')
            ->get()
            ->groupBy('module');
    }
}
