<?php

namespace App\Modules\Contacts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Contacts\Clusters\ContactsCluster;
use App\Modules\Contacts\Navigation\ContactsNavigation;
use Filament\Support\Icons\Heroicon;

class SuppliersPage extends BaseModulePage
{
    protected static ?string $cluster = ContactsCluster::class;

    protected static ?string $navigationLabel = 'Suppliers';

    protected static ?string $title = 'Suppliers';

    protected static ?string $slug = 'suppliers';

    protected static ?int $navigationSort = ContactsNavigation::SUPPLIERS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;
}
