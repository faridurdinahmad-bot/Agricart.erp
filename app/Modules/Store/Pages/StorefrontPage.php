<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class StorefrontPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Storefront';

    protected static ?string $title = 'Storefront';

    protected static ?string $slug = 'storefront';

    protected static ?int $navigationSort = StoreNavigation::STOREFRONT;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
}
