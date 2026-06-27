<?php

namespace App\Modules\Settings\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Authorization\Enums\UserRegistrationSource;
use App\Core\Authorization\UserProvisioner;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Core\Staff\Concerns\InteractsWithStaffForm;
use App\Models\Role;
use App\Models\User;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Actions\Action;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class UsersPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal, InteractsWithStaffForm;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $title = 'Users';

    protected static ?string $slug = 'users';

    protected static ?int $navigationSort = SettingsNavigation::USERS;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    public string $search = '';

    public string $statusFilter = '';

    public string $roleFilter = '';

    public function mount(): void
    {
        $this->resetStaffForm();
    }

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-users-page'];
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->with('role')
            ->when(filled($this->search), function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('staff_no', 'like', $term);
                });
            })
            ->when(filled($this->statusFilter), fn ($query) => $query->where('status', $this->statusFilter))
            ->when(filled($this->roleFilter), fn ($query) => $query->where('role_id', $this->roleFilter))
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function filterRoles()
    {
        return Role::query()->orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        unset($this->users);
    }

    public function updatedStatusFilter(): void
    {
        unset($this->users);
    }

    public function updatedRoleFilter(): void
    {
        unset($this->users);
    }

    public function addStaffAction(): Action
    {
        return Action::make('addStaff')
            ->label('Add Staff')
            ->icon(Heroicon::OutlinedPlus)
            ->color('primary')
            ->modalHeading('Add Staff')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.previews.staff-form', [
                'live' => true,
                'showStaffNumber' => true,
                'showJoinDate' => true,
                'staffForm' => $this->staffForm,
                'profilePhoto' => $this->profilePhoto,
                'cnicFront' => $this->cnicFront,
                'cnicBack' => $this->cnicBack,
            ]))
            ->stickyModalFooter()
            ->modalFooterActions([
                Action::make('cancelAddStaff')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                Action::make('saveAndAddNextStaff')
                    ->label('Save & Add Next')
                    ->outlined()
                    ->action(function (): void {
                        $this->saveStaff(addAnother: true);
                    }),
                Action::make('submitAddStaff')
                    ->label('Save')
                    ->color('primary')
                    ->action(function (): void {
                        $this->saveStaff();
                    }),
            ])
            ->before(function (): void {
                $this->resetStaffForm();
            });
    }

    public function saveStaff(bool $addAnother = false): void
    {
        $this->authorizePageAction(PermissionAction::Create);

        try {
            $payload = $this->validateStaffForm();

            UserProvisioner::createPending($payload, UserRegistrationSource::Admin);

            unset($this->users);

            if ($addAnother) {
                $this->resetStaffForm();
            }

            $this->completeModalSave(
                addAnother: $addAnother,
                title: 'Staff submitted for approval',
                body: 'The user has been created with Pending status and will appear in Approvals.',
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.settings.users-page'),
            ]);
    }
}
