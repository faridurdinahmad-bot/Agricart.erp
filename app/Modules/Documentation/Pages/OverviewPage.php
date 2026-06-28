<?php

namespace App\Modules\Documentation\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Documentation\Clusters\DocumentationCluster;
use App\Modules\Documentation\Navigation\DocumentationNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = DocumentationCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = DocumentationNavigation::OVERVIEW;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
