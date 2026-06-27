<?php

namespace App\Modules\Sales\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Sales\Clusters\SalesCluster;
use App\Modules\Sales\Navigation\SalesNavigation;
use Filament\Support\Icons\Heroicon;

class HeldSalesPage extends BaseModulePage
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $navigationLabel = 'Held Sales';

    protected static ?string $title = 'Held Sales';

    protected static ?string $slug = 'held-sales';

    protected static ?int $navigationSort = SalesNavigation::HELD_SALES;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedPauseCircle;
}
