<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class PagesPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $title = 'Pages';

    protected static ?string $slug = 'pages';

    protected static ?int $navigationSort = StoreNavigation::PAGES;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedDocument;
}
