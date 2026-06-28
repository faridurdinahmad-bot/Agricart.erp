<?php

namespace App\Modules\HR\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\HR\Clusters\HRCluster;
use App\Modules\HR\Navigation\HRNavigation;
use Filament\Support\Icons\Heroicon;

class ReportsPage extends BaseModulePage
{
    protected static ?string $cluster = HRCluster::class;

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports';

    protected static ?string $slug = 'reports';

    protected static ?int $navigationSort = HRNavigation::REPORTS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
}
