<?php

namespace App\Modules\Sales\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Sales\Clusters\SalesCluster;
use App\Modules\Sales\Navigation\SalesNavigation;
use Filament\Support\Icons\Heroicon;

class OrdersPage extends BaseModulePage
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $title = 'Orders';

    protected static ?string $slug = 'orders';

    protected static ?int $navigationSort = SalesNavigation::ORDERS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
}
