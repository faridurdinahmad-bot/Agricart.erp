<?php

namespace App\Modules\Reports\Navigation;

/**
 * Defines the Reports module submenu order.
 * Each page references its sort index from this map.
 */
class ReportsNavigation
{
    public const OVERVIEW = 1;

    public const STOCK_REPORT = 2;

    public const CUSTOMER_LEDGER = 3;

    public const SUPPLIER_LEDGER = 4;

    public const SALES_SUMMARY = 5;

    public const PROFIT_AND_LOSS = 6;

    public const EXPENSE_REPORT = 7;

    public const CASH_REPORT = 8;

    public const SALES_REPORT = 9;

    public const PURCHASE_REPORT = 10;
}
