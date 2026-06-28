<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class SalesReportPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Sales Report';

    protected static ?string $title = 'Sales Report';

    protected static ?string $slug = 'sales-report';

    protected static ?int $navigationSort = ReportsNavigation::SALES_REPORT;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
}
