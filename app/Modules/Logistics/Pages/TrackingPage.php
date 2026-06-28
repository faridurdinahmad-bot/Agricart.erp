<?php

namespace App\Modules\Logistics\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Logistics\Clusters\LogisticsCluster;
use App\Modules\Logistics\Navigation\LogisticsNavigation;
use Filament\Support\Icons\Heroicon;

class TrackingPage extends BaseModulePage
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $navigationLabel = 'Tracking';

    protected static ?string $title = 'Tracking';

    protected static ?string $slug = 'tracking';

    protected static ?int $navigationSort = LogisticsNavigation::TRACKING;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
}
