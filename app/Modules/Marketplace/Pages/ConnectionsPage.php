<?php

namespace App\Modules\Marketplace\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Marketplace\Clusters\MarketplaceCluster;
use App\Modules\Marketplace\Navigation\MarketplaceNavigation;
use Filament\Support\Icons\Heroicon;

class ConnectionsPage extends BaseModulePage
{
    protected static ?string $cluster = MarketplaceCluster::class;

    protected static ?string $navigationLabel = 'Connections';

    protected static ?string $title = 'Connections';

    protected static ?string $slug = 'connections';

    protected static ?int $navigationSort = MarketplaceNavigation::CONNECTIONS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;
}
