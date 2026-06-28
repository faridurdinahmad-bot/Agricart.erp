<?php

namespace App\Core\Authorization;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RoleManager
{
    public static function ensureSuperAdminRole(): Role
    {
        PermissionGate::syncIfStale();

        $role = Role::query()->firstOrCreate(
            ['slug' => PermissionCatalog::SUPER_ADMIN_SLUG],
            [
                'name' => 'Super Admin',
                'is_system' => true,
                'is_active' => true,
                'description' => 'Permanent system role with full access.',
            ],
        );

        $role->update([
            'name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        $role->permissions()->sync(Permission::query()->pluck('id'));

        PermissionGate::flushForRole($role->refresh());

        return $role;
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    public static function create(string $name, ?string $description, array $permissionKeys): Role
    {
        $role = Role::query()->create([
            'name' => $name,
            'slug' => self::uniqueSlug($name),
            'is_system' => false,
            'is_active' => true,
            'description' => $description,
        ]);

        self::syncPermissions($role, $permissionKeys);

        return $role;
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    public static function update(Role $role, string $name, ?string $description, bool $isActive, array $permissionKeys): Role
    {
        self::guardProtectedRole($role, 'update');

        $role->update([
            'name' => $name,
            'description' => $description,
            'is_active' => $role->isProtected() ? true : $isActive,
        ]);

        if (! $role->isProtected()) {
            self::syncPermissions($role, $permissionKeys);
        } else {
            PermissionGate::flushForRole($role->refresh());
        }

        return $role->refresh();
    }

    public static function delete(Role $role): void
    {
        self::guardProtectedRole($role, 'delete');

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'This role is assigned to users and cannot be deleted.',
            ]);
        }

        $role->delete();
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    public static function syncPermissions(Role $role, array $permissionKeys): void
    {
        if ($role->isProtected()) {
            $role->permissions()->sync(Permission::query()->pluck('id'));
            PermissionGate::flushForRole($role->refresh());

            return;
        }

        $permissionIds = Permission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id');

        $role->permissions()->sync($permissionIds);

        PermissionGate::flushForRole($role->refresh());
    }

    public static function toggleActive(Role $role): Role
    {
        self::guardProtectedRole($role, 'deactivate');

        $role->update([
            'is_active' => ! $role->is_active,
        ]);

        PermissionGate::flushForRole($role->refresh());

        return $role;
    }

    public static function guardProtectedRole(Role $role, string $action): void
    {
        if (! $role->isProtected()) {
            return;
        }

        throw ValidationException::withMessages([
            'role' => "The Super Admin role cannot be {$action}d.",
        ]);
    }

    protected static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
