<?php

namespace App\Modules\Contacts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Contacts\Clusters\ContactsCluster;
use App\Modules\Contacts\Navigation\ContactsNavigation;
use Filament\Support\Icons\Heroicon;

class CustomersPage extends BaseModulePage
{
    protected static ?string $cluster = ContactsCluster::class;

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $title = 'Customers';

    protected static ?string $slug = 'customers';

    protected static ?int $navigationSort = ContactsNavigation::CUSTOMERS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
}
