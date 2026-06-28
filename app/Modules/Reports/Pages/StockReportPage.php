<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class StockReportPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Stock Report';

    protected static ?string $title = 'Stock Report';

    protected static ?string $slug = 'stock-report';

    protected static ?int $navigationSort = ReportsNavigation::STOCK_REPORT;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
}
