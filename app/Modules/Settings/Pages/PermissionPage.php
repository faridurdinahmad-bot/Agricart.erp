<?php

namespace App\Modules\Settings\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Authorization\PermissionCatalog;
use App\Core\Authorization\RoleManager;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Role;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class PermissionPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $title = 'Roles';

    protected static ?string $slug = 'permission';

    protected static ?int $navigationSort = SettingsNavigation::PERMISSION;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public ?int $selectedRoleId = null;

    public string $roleName = '';

    public string $roleDescription = '';

    public bool $roleIsActive = true;

    /** @var list<string> */
    public array $selectedPermissions = [];

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-roles-page'];
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->withCount('users')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function permissionGroups(): array
    {
        return PermissionCatalog::grouped();
    }

    #[Computed]
    public function selectedRole(): ?Role
    {
        if (! $this->selectedRoleId) {
            return null;
        }

        return Role::query()->find($this->selectedRoleId);
    }

    public function addRoleAction(): Action
    {
        return $this->configureRoleFormAction(
            Action::make('addRole')
                ->label('Add Role')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->before(function (): void {
                    $this->resetRoleForm();
                }),
        );
    }

    public function roleFormAction(): Action
    {
        return $this->configureRoleFormAction(Action::make('roleForm'));
    }

    public function openEditRole(int $roleId): void
    {
        $this->authorizePageAction(PermissionAction::Update);
        $this->loadRole($roleId);
        $this->mountAction('roleForm');
    }

    protected function configureRoleFormAction(Action $action): Action
    {
        return $action
            ->modalHeading(fn (): string => $this->selectedRoleId ? 'Edit Role' : 'Add Role')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.settings.role-form'))
            ->stickyModalFooter()
            ->modalFooterActions([
                Action::make('cancelRoleForm')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                Action::make('saveAndAddNextRole')
                    ->label('Save & Add Next')
                    ->outlined()
                    ->visible(fn (): bool => ! ($this->selectedRole?->isProtected() ?? false))
                    ->action(function (): void {
                        $this->saveRole(addAnother: true);
                    }),
                Action::make('submitRoleForm')
                    ->label(fn (): string => $this->selectedRoleId ? 'Save' : 'Create Role')
                    ->color('primary')
                    ->visible(fn (): bool => ! ($this->selectedRole?->isProtected() ?? false))
                    ->action(function (): void {
                        $this->saveRole();
                    }),
            ]);
    }

    public function saveRole(bool $addAnother = false): void
    {
        if ($this->selectedRoleId && $this->selectedRole()?->isProtected()) {
            Notification::make()
                ->title('Super Admin cannot be modified')
                ->warning()
                ->send();

            return;
        }

        $this->authorizePageAction(
            $this->selectedRoleId ? PermissionAction::Update : PermissionAction::Create,
        );

        $this->validate([
            'roleName' => ['required', 'string', 'max:255'],
            'roleDescription' => ['nullable', 'string', 'max:1000'],
            'roleIsActive' => ['boolean'],
            'selectedPermissions' => ['array'],
        ]);

        try {
            if ($this->selectedRoleId) {
                $role = Role::query()->findOrFail($this->selectedRoleId);

                RoleManager::update(
                    $role,
                    $this->roleName,
                    $this->roleDescription,
                    $this->roleIsActive,
                    $this->selectedPermissions,
                );

                $message = 'Role updated';
            } else {
                RoleManager::create(
                    $this->roleName,
                    $this->roleDescription,
                    $this->selectedPermissions,
                );

                $message = 'Role created';
            }

            unset($this->roles);
            $this->resetRoleForm();

            $this->completeModalSave(
                addAnother: $addAnother,
                title: $message,
                refreshNavigation: ! $addAnother,
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorizePageAction(PermissionAction::Delete);

        try {
            $role = Role::query()->findOrFail($roleId);
            RoleManager::delete($role);

            Notification::make()->title('Role deleted')->success()->send();

            unset($this->roles);

            $this->redirect(static::getUrl(), navigate: false);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function toggleRoleActive(int $roleId): void
    {
        $this->authorizePageAction(PermissionAction::Update);

        try {
            $role = Role::query()->findOrFail($roleId);
            RoleManager::toggleActive($role);

            Notification::make()
                ->title($role->fresh()->is_active ? 'Role activated' : 'Role deactivated')
                ->success()
                ->send();

            unset($this->roles);

            $this->redirect(static::getUrl(), navigate: false);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function selectAllPermissions(): void
    {
        if ($this->selectedRole()?->isProtected()) {
            return;
        }

        $this->selectedPermissions = PermissionCatalog::allKeys();
    }

    public function unselectAllPermissions(): void
    {
        if ($this->selectedRole()?->isProtected()) {
            return;
        }

        $this->selectedPermissions = [];
    }

    public function selectModulePermissions(string $module): void
    {
        if ($this->selectedRole()?->isProtected()) {
            return;
        }

        $moduleKeys = collect(PermissionCatalog::entries())
            ->filter(fn (array $entry): bool => $entry['module'] === $module)
            ->flatMap(fn (array $entry): array => collect(PermissionAction::all())
                ->map(fn (PermissionAction $action): string => PermissionCatalog::key($entry['module'], $entry['page'], $action))
                ->all())
            ->all();

        $this->selectedPermissions = array_values(array_unique([
            ...$this->selectedPermissions,
            ...$moduleKeys,
        ]));
    }

    public function unselectModulePermissions(string $module): void
    {
        if ($this->selectedRole()?->isProtected()) {
            return;
        }

        $this->selectedPermissions = collect($this->selectedPermissions)
            ->reject(fn (string $key): bool => str_starts_with($key, "{$module}."))
            ->values()
            ->all();
    }

    protected function resetRoleForm(): void
    {
        $this->selectedRoleId = null;
        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleIsActive = true;
        $this->selectedPermissions = [];
    }

    protected function loadRole(int $roleId): void
    {
        $role = Role::query()->with('permissions')->findOrFail($roleId);

        $this->selectedRoleId = $role->id;
        $this->roleName = $role->name;
        $this->roleDescription = (string) $role->description;
        $this->roleIsActive = $role->is_active;
        $this->selectedPermissions = $role->permissions->pluck('key')->all();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.settings.roles-page'),
            ]);
    }
}
