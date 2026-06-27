<?php

namespace App\Core\Modules;

/**
 * Canonical sidebar ordering for Agricart modules.
 *
 * Dashboard is registered via AdminPanelProvider (Filament default sort: -2).
 * Registry modules use ascending sort values between Dashboard and Documentation.
 */
final class ModuleNavigationSort
{
    /** Filament Dashboard default — reserved, do not assign to registry modules. */
    public const DASHBOARD = -2;

    public const APPROVALS = 2;

    public const CONTACTS = 3;

    public const CATALOG = 4;

    public const INVENTORY = 5;

    public const SALES = 6;

    public const STORE = 7;

    public const LOGISTICS = 8;

    public const REPORTS = 9;

    public const HR = 10;

    public const ACCOUNTS = 11;

    public const MARKETPLACE = 12;

    /** Reserved (formerly Service module). */
    // 13

    public const SETTINGS = 14;

    public const DOCUMENTATION = 15;

    /** Assign to the next module, then increment. */
    public const NEXT_AVAILABLE = 16;
}
