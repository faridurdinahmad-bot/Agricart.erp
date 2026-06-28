<?php

namespace App\Modules\Sales\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Sales\Clusters\SalesCluster;
use App\Modules\Sales\Navigation\SalesNavigation;
use Filament\Support\Icons\Heroicon;

class QuotationsPage extends BaseModulePage
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $navigationLabel = 'Quotations';

    protected static ?string $title = 'Quotations';

    protected static ?string $slug = 'quotations';

    protected static ?int $navigationSort = SalesNavigation::QUOTATIONS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
}
