<?php

namespace App\Modules\Sales\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Sales\Clusters\SalesCluster;
use App\Modules\Sales\Navigation\SalesNavigation;
use Filament\Support\Icons\Heroicon;

class PosPage extends BaseModulePage
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $navigationLabel = 'POS';

    protected static ?string $title = 'POS';

    protected static ?string $slug = 'pos';

    protected static ?int $navigationSort = SalesNavigation::POS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedComputerDesktop;
}
