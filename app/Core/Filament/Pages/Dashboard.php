<?php

namespace App\Core\Filament\Pages;

use App\Core\Authorization\PermissionCatalog;
use App\Models\User;
use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasPermission(PermissionCatalog::dashboardViewKey());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function rendering(): void
    {
        view()->share('agricartLayoutBreadcrumbs', filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : []);
        view()->share('agricartLayoutSubNavigation', []);
    }
}
