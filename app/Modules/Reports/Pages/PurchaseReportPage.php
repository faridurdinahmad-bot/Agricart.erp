<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class PurchaseReportPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Purchase Report';

    protected static ?string $title = 'Purchase Report';

    protected static ?string $slug = 'purchase-report';

    protected static ?int $navigationSort = ReportsNavigation::PURCHASE_REPORT;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;
}
