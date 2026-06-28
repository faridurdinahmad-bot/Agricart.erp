<?php

namespace App\Modules\Settings\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Support\Icons\Heroicon;

class PurchasePricingPage extends BaseModulePage
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $title = 'Purchase Pricing';

    protected static ?string $slug = 'purchase-pricing';

    protected static ?int $navigationSort = SettingsNavigation::PURCHASE_PRICING;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
}
