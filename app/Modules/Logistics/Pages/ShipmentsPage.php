<?php

namespace App\Modules\Logistics\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Logistics\Clusters\LogisticsCluster;
use App\Modules\Logistics\Navigation\LogisticsNavigation;
use Filament\Support\Icons\Heroicon;

class ShipmentsPage extends BaseModulePage
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $navigationLabel = 'Shipments';

    protected static ?string $title = 'Shipments';

    protected static ?string $slug = 'shipments';

    protected static ?int $navigationSort = LogisticsNavigation::SHIPMENTS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;
}
