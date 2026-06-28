# Agricart Module Template

Copy this folder to `app/Modules/{ModuleName}/` and replace all placeholders.

## Placeholders

| Placeholder | Example |
|-------------|---------|
| `{{ModuleName}}` | `Products` |
| `{{module_id}}` | `products` |
| `{{ModuleLabel}}` | `Products` |
| `{{namespace}}` | `App\\Modules\\Products` |
| `{{navigation_sort}}` | `3` (use `ModuleNavigationSort::NEXT_AVAILABLE`, then increment) |
| `{{Heroicon}}` | `Heroicon::OutlinedCube` |

## Steps

1. Copy `stubs/module/` → `app/Modules/{ModuleName}/`
2. Rename all `.stub` files (remove `.stub` suffix)
3. Replace placeholders in every file
4. Add submenu pages under `Pages/` (extend `BaseModulePage`)
5. Register sort constants in `Navigation/{ModuleName}Navigation.php`
6. Assign a unique `navigationSort` on the cluster using `ModuleNavigationSort::NEXT_AVAILABLE` (increment for each module)

Permissions sync **automatically** when pages are added or removed. Use `php artisan agricart:sync-permissions` only to force a manual resync.

Modules are **auto-discovered** from `app/Modules/{ModuleName}/{ModuleName}Module.php`.
No registration in `AppServiceProvider` is required.

## Font & theme

- Panel font is configured in `AdminPanelProvider` via `SystemFontProvider` (Arial, no CDN)
- All colors live in `resources/css/filament/admin/theme.css` only
- Do not re-enable Filament Inter assets in `public/fonts/filament/filament/inter/index.css`

## Rules (Phase 2+)

- Extend `BaseModuleCluster` and `BaseModulePage` only
- No business logic, forms, migrations, or models in UI-only phases
- Colors/fonts only via `resources/css/filament/admin/theme.css`
- Do not modify existing modules when adding a new one

## Sidebar order

| # | Module | Sort constant |
|---|--------|---------------|
| 1 | Dashboard (Filament core) | `-2` |
| 2 | Approvals | `ModuleNavigationSort::APPROVALS` (2) |
| 3 | Contacts | `ModuleNavigationSort::CONTACTS` (3) |
| 4 | Catalog | `ModuleNavigationSort::CATALOG` (4) |
| 5 | Inventory | `ModuleNavigationSort::INVENTORY` (5) |
| 6 | Sales | `ModuleNavigationSort::SALES` (6) |
| 7 | Store | `ModuleNavigationSort::STORE` (7) |
| 8 | Logistics | `ModuleNavigationSort::LOGISTICS` (8) |
| 9 | Reports | `ModuleNavigationSort::REPORTS` (9) |
| 10 | HR | `ModuleNavigationSort::HR` (10) |
| 11 | Accounts | `ModuleNavigationSort::ACCOUNTS` (11) |
| 12 | Marketplace | `ModuleNavigationSort::MARKETPLACE` (12) |
| 14 | Settings | `ModuleNavigationSort::SETTINGS` (14) |
| 15 | Documentation (last) | `ModuleNavigationSort::DOCUMENTATION` (15) |

**Rule:** Insert every new module before Documentation using `NEXT_AVAILABLE` (currently 16).
