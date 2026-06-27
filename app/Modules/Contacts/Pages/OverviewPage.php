<?php

namespace App\Modules\Contacts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Contacts\Clusters\ContactsCluster;
use App\Modules\Contacts\Navigation\ContactsNavigation;
use Filament\Support\Icons\Heroicon;

class OverviewPage extends BaseModulePage
{
    protected static ?string $cluster = ContactsCluster::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Overview';

    protected static ?string $slug = 'overview';

    protected static ?int $navigationSort = ContactsNavigation::OVERVIEW;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;
}
