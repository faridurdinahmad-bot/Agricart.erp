<?php

namespace App\Modules\Inventory\Navigation;

/**
 * Defines the Inventory module submenu order.
 * Each page references its sort index from this map.
 */
class InventoryNavigation
{
    public const OVERVIEW = 1;

    public const PLANNING = 2;

    public const QUOTATIONS = 3;

    public const PURCHASES = 4;

    public const PAYMENTS = 5;

    public const REORDER = 6;

    public const PRICE_TAGS = 7;
}
