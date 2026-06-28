<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class HomepagePage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Homepage';

    protected static ?string $title = 'Homepage';

    protected static ?string $slug = 'homepage';

    protected static ?int $navigationSort = StoreNavigation::HOMEPAGE;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;
}
