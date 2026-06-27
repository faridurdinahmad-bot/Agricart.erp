<?php

namespace App\Modules\Sales\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Sales\Clusters\SalesCluster;
use App\Modules\Sales\Navigation\SalesNavigation;
use Filament\Support\Icons\Heroicon;

class ReturnsPage extends BaseModulePage
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $navigationLabel = 'Returns';

    protected static ?string $title = 'Returns';

    protected static ?string $slug = 'returns';

    protected static ?int $navigationSort = SalesNavigation::RETURNS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;
}
