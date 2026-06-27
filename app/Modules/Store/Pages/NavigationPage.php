<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class NavigationPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Navigation';

    protected static ?string $title = 'Navigation';

    protected static ?string $slug = 'navigation';

    protected static ?int $navigationSort = StoreNavigation::NAVIGATION;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedMap;
}
