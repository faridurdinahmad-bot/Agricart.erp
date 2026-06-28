<?php

namespace App\Modules\Marketplace\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Marketplace\Clusters\MarketplaceCluster;
use App\Modules\Marketplace\Navigation\MarketplaceNavigation;
use Filament\Support\Icons\Heroicon;

class PricingRulesPage extends BaseModulePage
{
    protected static ?string $cluster = MarketplaceCluster::class;

    protected static ?string $navigationLabel = 'Pricing Rules';

    protected static ?string $title = 'Pricing Rules';

    protected static ?string $slug = 'pricing-rules';

    protected static ?int $navigationSort = MarketplaceNavigation::PRICING_RULES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
}
