<?php

namespace App\Core\Authorization\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Authorization\PermissionCatalog;
use App\Core\Authorization\PermissionGate;
use App\Models\Permission;
use App\Models\Role;

trait HasAgricartPermissions
{
    public function isSuperAdmin(): bool
    {
        return $this->role?->isSuperAdmin() ?? false;
    }

    public function hasPermission(string $key): bool
    {
        return PermissionGate::forUser($this)->allows($key);
    }

    public function hasPagePermission(string $module, string $page, PermissionAction $action): bool
    {
        return $this->hasPermission(PermissionCatalog::key($module, $page, $action));
    }

    public function canViewModule(string $module): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return PermissionGate::forUser($this)->canViewModule($module);
    }

    public function canViewPage(string $module, string $page): bool
    {
        return $this->hasPagePermission($module, $page, PermissionAction::View);
    }

    public function canManageSuperAdminAccounts(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageUser(self $target): bool
    {
        if ($target->isSuperAdmin() && ! $this->canManageSuperAdminAccounts()) {
            return false;
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public function permissionKeys(): array
    {
        return PermissionGate::forUser($this)->keys();
    }

    public function syncRolePermissions(Role $role, array $permissionKeys): void
    {
        if ($role->isProtected()) {
            return;
        }

        $permissionIds = Permission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id');

        $role->permissions()->sync($permissionIds);
    }
}
