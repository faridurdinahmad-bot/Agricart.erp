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
6. Register module in `AppServiceProvider`:

```php
ModuleRegistry::register(ProductsModule::class);
```

7. Assign a unique `navigationSort` on the cluster using `ModuleNavigationSort::NEXT_AVAILABLE` (increment for each module)

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

| Module | Sort constant |
|--------|---------------|
| Dashboard (Filament core) | `-2` |
| Settings | `ModuleNavigationSort::SETTINGS` (2) |
| Next module | `ModuleNavigationSort::NEXT_AVAILABLE` (3) |
