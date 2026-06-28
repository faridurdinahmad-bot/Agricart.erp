<?php

namespace App\Modules\Logistics\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Logistics\Clusters\LogisticsCluster;
use App\Modules\Logistics\Navigation\LogisticsNavigation;
use Filament\Support\Icons\Heroicon;

class DeliveryZonesPage extends BaseModulePage
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $navigationLabel = 'Delivery Zones';

    protected static ?string $title = 'Delivery Zones';

    protected static ?string $slug = 'delivery-zones';

    protected static ?int $navigationSort = LogisticsNavigation::DELIVERY_ZONES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;
}
