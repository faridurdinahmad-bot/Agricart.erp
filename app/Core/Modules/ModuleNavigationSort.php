<?php

namespace App\Core\Modules;

/**
 * Canonical sidebar ordering for Agricart modules.
 *
 * Dashboard is registered via AdminPanelProvider (Filament default sort: -2).
 * Each registry module must use a unique, ascending navigationSort on its cluster.
 */
final class ModuleNavigationSort
{
    /** Filament Dashboard default — reserved, do not assign to registry modules. */
    public const DASHBOARD = -2;

    public const SETTINGS = 2;

    /** Assign this value to the next module cluster, then increment for each new module. */
    public const NEXT_AVAILABLE = 3;
}
