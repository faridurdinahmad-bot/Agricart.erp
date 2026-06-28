<?php

namespace App\Modules\Marketplace\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Marketplace\Clusters\MarketplaceCluster;
use App\Modules\Marketplace\Navigation\MarketplaceNavigation;
use Filament\Support\Icons\Heroicon;

class SyncLogsPage extends BaseModulePage
{
    protected static ?string $cluster = MarketplaceCluster::class;

    protected static ?string $navigationLabel = 'Sync Logs';

    protected static ?string $title = 'Sync Logs';

    protected static ?string $slug = 'sync-logs';

    protected static ?int $navigationSort = MarketplaceNavigation::SYNC_LOGS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
}
