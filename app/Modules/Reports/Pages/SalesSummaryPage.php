<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class SalesSummaryPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Sales Summary';

    protected static ?string $title = 'Sales Summary';

    protected static ?string $slug = 'sales-summary';

    protected static ?int $navigationSort = ReportsNavigation::SALES_SUMMARY;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBar;
}
