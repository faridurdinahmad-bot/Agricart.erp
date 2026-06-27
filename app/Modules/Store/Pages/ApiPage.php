<?php

namespace App\Modules\Store\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Store\Clusters\StoreCluster;
use App\Modules\Store\Navigation\StoreNavigation;
use Filament\Support\Icons\Heroicon;

class ApiPage extends BaseModulePage
{
    protected static ?string $cluster = StoreCluster::class;

    protected static ?string $navigationLabel = 'API';

    protected static ?string $title = 'API';

    protected static ?string $slug = 'api';

    protected static ?int $navigationSort = StoreNavigation::API;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCodeBracket;
}
