<?php

namespace App\Core\Filament\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    public function rendering(): void
    {
        view()->share('agricartLayoutBreadcrumbs', filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : []);
        view()->share('agricartLayoutSubNavigation', []);
    }
}
