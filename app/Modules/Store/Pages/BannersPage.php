<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class BannersPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Banners';

    protected static ?string $title = 'Banners';

    protected static ?string $slug = 'banners';

    protected static ?int $navigationSort = StoreNavigation::BANNERS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedPhoto;
}
