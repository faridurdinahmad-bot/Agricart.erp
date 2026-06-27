<?php

namespace App\Modules\Accounts\Pages;

use App\Core\Filament\Pages\BaseModulePage;
use App\Modules\Accounts\Clusters\AccountsCluster;
use App\Modules\Accounts\Navigation\AccountsNavigation;
use Filament\Support\Icons\Heroicon;

class BankAccountsPage extends BaseModulePage
{
    protected static ?string $cluster = AccountsCluster::class;

    protected static ?string $navigationLabel = 'Bank Accounts';

    protected static ?string $title = 'Bank Accounts';

    protected static ?string $slug = 'bank-accounts';

    protected static ?int $navigationSort = AccountsNavigation::BANK_ACCOUNTS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
}
