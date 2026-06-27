<?php

namespace App\Modules\Approvals\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Authorization\Enums\UserStatus;
use App\Core\Authorization\PermissionCatalog;
use App\Core\Authorization\UserProvisioner;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Role;
use App\Models\User;
use App\Modules\Approvals\Clusters\ApprovalsCluster;
use App\Modules\Approvals\Navigation\ApprovalsNavigation;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class StaffApprovalPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal;

    protected static ?string $cluster = ApprovalsCluster::class;

    protected static ?string $navigationLabel = 'Staff Approval';

    protected static ?string $title = 'Staff Approval';

    protected static ?string $slug = 'staff-approval';

    protected static ?int $navigationSort = ApprovalsNavigation::STAFF_APPROVAL;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUserGroup;

    /** @var array<int, int|null> */
    public array $approvalRoles = [];

    public ?int $rejectingUserId = null;

    public ?int $returningUserId = null;

    public ?int $viewingUserId = null;

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-approvals-page'];
    }

    #[Computed]
    public function pendingUsers()
    {
        return User::query()
            ->where('status', UserStatus::Pending)
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function viewingUser(): ?User
    {
        if (! $this->viewingUserId) {
            return null;
        }

        return User::query()->find($this->viewingUserId);
    }

    #[Computed]
    public function assignableRoles()
    {
        $query = Role::query()
            ->where('is_active', true)
            ->orderByDesc('is_system')
            ->orderBy('name');

        if (! auth()->user()?->isSuperAdmin()) {
            $query->where('slug', '!=', PermissionCatalog::SUPER_ADMIN_SLUG);
        }

        return $query->get();
    }

    public function approveUser(int $userId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        $roleId = $this->approvalRoles[$userId] ?? null;

        if (! $roleId) {
            Notification::make()
                ->title('Select a role')
                ->body('Assign a role before approving this staff member.')
                ->warning()
                ->send();

            return;
        }

        try {
            $user = User::query()->findOrFail($userId);
            $role = Role::query()->findOrFail($roleId);
            /** @var User $approver */
            $approver = auth()->user();

            UserProvisioner::approve($user, $role, $approver);

            Notification::make()
                ->title('Staff approved')
                ->body("{$user->name} is now active with the {$role->name} role.")
                ->success()
                ->send();

            unset($this->approvalRoles[$userId], $this->pendingUsers);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Approval failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function openViewProfile(int $userId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->viewingUserId = $userId;
        unset($this->viewingUser);
        $this->mountAction('viewStaffProfile');
    }

    public function viewStaffProfileAction(): Action
    {
        return Action::make('viewStaffProfile')
            ->label('View Profile')
            ->modalHeading(fn (): string => 'Staff Profile — '.($this->viewingUser?->name ?? ''))
            ->modalWidth(Width::SevenExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.approvals.staff-profile-view', [
                'profileUser' => $this->viewingUser,
            ]))
            ->modalFooterActions([
                Action::make('closeViewStaffProfile')
                    ->label('Close')
                    ->color('gray')
                    ->close(),
            ]);
    }

    public function openReturnModal(int $userId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->returningUserId = $userId;
        $this->mountAction('returnStaff');
    }

    public function returnStaffAction(): Action
    {
        return Action::make('returnStaff')
            ->label('Return for Correction')
            ->color('warning')
            ->modalHeading('Return for Correction')
            ->modalWidth(Width::Medium)
            ->schema([
                Textarea::make('correctionRemarks')
                    ->label('Admin Remarks')
                    ->required()
                    ->rows(4),
            ])
            ->modalFooterActions([
                Action::make('cancelReturnStaff')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                Action::make('submitReturnStaff')
                    ->label('Return for Correction')
                    ->color('warning')
                    ->action(function (array $data): void {
                        $this->returnStaff($data['correctionRemarks'] ?? '');
                    }),
            ]);
    }

    public function returnStaff(string $correctionRemarks): void
    {
        if (! $this->returningUserId) {
            return;
        }

        try {
            $user = User::query()->findOrFail($this->returningUserId);
            /** @var User $approver */
            $approver = auth()->user();

            UserProvisioner::returnForCorrection($user, $approver, $correctionRemarks);

            unset($this->approvalRoles[$this->returningUserId], $this->pendingUsers);
            $this->returningUserId = null;

            $this->completeModalSave(
                title: 'Application returned',
                body: "{$user->name} can now edit and resubmit their application.",
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Return failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function openRejectModal(int $userId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->rejectingUserId = $userId;
        $this->mountAction('rejectStaff');
    }

    public function rejectStaffAction(): Action
    {
        return Action::make('rejectStaff')
            ->label('Reject')
            ->color('danger')
            ->modalHeading('Reject Staff Registration')
            ->modalWidth(Width::Medium)
            ->schema([
                Textarea::make('rejectionReason')
                    ->label('Rejection Reason')
                    ->required()
                    ->rows(4),
            ])
            ->modalFooterActions([
                Action::make('cancelRejectStaff')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                Action::make('submitRejectStaff')
                    ->label('Reject')
                    ->color('danger')
                    ->action(function (array $data): void {
                        $this->rejectStaff($data['rejectionReason'] ?? '');
                    }),
            ]);
    }

    public function rejectStaff(string $rejectionReason): void
    {
        if (! $this->rejectingUserId) {
            return;
        }

        try {
            $user = User::query()->findOrFail($this->rejectingUserId);
            /** @var User $approver */
            $approver = auth()->user();

            UserProvisioner::reject($user, $approver, $rejectionReason);

            unset($this->approvalRoles[$this->rejectingUserId], $this->pendingUsers);
            $this->rejectingUserId = null;

            $this->completeModalSave(
                title: 'Staff rejected',
                body: "{$user->name} has been rejected and cannot log in.",
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Rejection failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.approvals.staff-approval-page'),
            ]);
    }
}
