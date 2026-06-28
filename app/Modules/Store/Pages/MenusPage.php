<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class MenusPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Menus';

    protected static ?string $title = 'Menus';

    protected static ?string $slug = 'menus';

    protected static ?int $navigationSort = StoreNavigation::MENUS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;
}
