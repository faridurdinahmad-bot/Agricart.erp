<?php

namespace App\Modules\Accounts\Navigation;

/**
 * Defines the Accounts module submenu order.
 * Each page references its sort index from this map.
 */
class AccountsNavigation
{
    public const OVERVIEW = 1;

    public const EXPENSE_CATEGORIES = 2;

    public const EXPENSES = 3;

    public const CASH_BOOK = 4;

    public const BANK_ACCOUNTS = 5;

    public const TRANSFERS = 6;

    public const FINANCIAL_REPORTS = 7;
}
