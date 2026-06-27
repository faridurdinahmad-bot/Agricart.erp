<?php

namespace App\Core\Authorization\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\User;
use Filament\Clusters\Cluster;

trait AuthorizesPageActions
{
    protected function authorizePageAction(PermissionAction $action, ?string $module = null, ?string $page = null): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $module ??= $this->pageModuleSlug();
        $page ??= $this->pageSlug();

        if (! $user->hasPagePermission($module, $page, $action)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    protected function pageModuleSlug(): string
    {
        $clusterClass = static::getCluster();

        if ($clusterClass && is_subclass_of($clusterClass, Cluster::class)) {
            return $clusterClass::getSlug();
        }

        return '';
    }

    protected function pageSlug(): string
    {
        return static::getSlug();
    }
}
