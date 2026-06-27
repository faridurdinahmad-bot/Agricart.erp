<?php

namespace App\Modules\Reports\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Reports\Clusters\ReportsCluster;
use App\Modules\Reports\Navigation\ReportsNavigation;
use Filament\Support\Icons\Heroicon;

class ProfitAndLossPage extends BaseModulePage
{
    protected static ?string $cluster = ReportsCluster::class;

    protected static ?string $navigationLabel = 'Profit & Loss';

    protected static ?string $title = 'Profit & Loss';

    protected static ?string $slug = 'profit-and-loss';

    protected static ?int $navigationSort = ReportsNavigation::PROFIT_AND_LOSS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBarSquare;
}
