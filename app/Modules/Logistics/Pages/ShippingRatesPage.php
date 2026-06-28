<?php

namespace App\Modules\Logistics\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Logistics\Clusters\LogisticsCluster;
use App\Modules\Logistics\Navigation\LogisticsNavigation;
use Filament\Support\Icons\Heroicon;

class ShippingRatesPage extends BaseModulePage
{
    protected static ?string $cluster = LogisticsCluster::class;

    protected static ?string $navigationLabel = 'Shipping Rates';

    protected static ?string $title = 'Shipping Rates';

    protected static ?string $slug = 'shipping-rates';

    protected static ?int $navigationSort = LogisticsNavigation::SHIPPING_RATES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
}
