<?php

namespace App\Modules\Approvals\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Catalog\BrandDeletionRequest;
use App\Modules\Approvals\Clusters\ApprovalsCluster;
use App\Modules\Approvals\Navigation\ApprovalsNavigation;
use App\Modules\Catalog\Concerns\InteractsWithBrandView;
use App\Modules\Catalog\Dto\BrandDeletionImpact;
use App\Modules\Catalog\Services\BrandDeletionImpactAnalyzer;
use App\Modules\Catalog\Services\BrandDeletionService;
use App\Modules\Catalog\Services\BrandImageStorage;
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

class BrandDeleteRequestsPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal, InteractsWithBrandView;

    protected static ?string $cluster = ApprovalsCluster::class;

    protected static ?string $navigationLabel = 'Brand Delete Requests';

    protected static ?string $title = 'Brand Delete Requests';

    protected static ?string $slug = 'brand-delete-requests';

    protected static ?int $navigationSort = ApprovalsNavigation::BRAND_DELETE_REQUESTS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    public ?int $viewingRequestId = null;

    public ?int $reviewingRequestId = null;

    public ?int $returningRequestId = null;

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-approvals-page', 'agricart-brand-delete-requests-page'];
    }

    #[Computed]
    public function pendingDeletionRequests()
    {
        return BrandDeletionRequest::query()
            ->with(['brand', 'requestedByUser'])
            ->where('status', EntityDeletionRequestStatus::Pending)
            ->orderByDesc('requested_at')
            ->get();
    }

    #[Computed]
    public function viewingRequest(): ?BrandDeletionRequest
    {
        if (! $this->viewingRequestId) {
            return null;
        }

        return BrandDeletionRequest::query()
            ->with(['brand.categories', 'requestedByUser'])
            ->find($this->viewingRequestId);
    }

    #[Computed]
    public function viewingRequestImpact(): ?BrandDeletionImpact
    {
        if (! $this->viewingRequest?->brand) {
            return null;
        }

        return BrandDeletionImpactAnalyzer::analyze($this->viewingRequest->brand);
    }

    public function openViewDeletionRequest(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->viewingRequestId = $requestId;
        unset($this->viewingRequest, $this->viewingRequestImpact);
        $this->mountAction('viewBrandDeletionRequest');
    }

    public function openViewBrandFromRequest(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        $request = BrandDeletionRequest::query()->findOrFail($requestId);

        if (! $request->brand_id) {
            return;
        }

        $this->viewingBrandId = $request->brand_id;
        unset($this->viewingBrand, $this->viewingBrandAssignedCategories, $this->viewingBrandImageDetails, $this->viewingBrandPromptInfo);
        $this->mountAction('viewBrand');
    }

    public function viewBrandDeletionRequestAction(): Action
    {
        return Action::make('viewBrandDeletionRequest')
            ->modalHeading(fn (): string => 'Deletion Impact — '.($this->viewingRequest?->brand?->code ?? ''))
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.approvals.brand-deletion-impact', [
                'request' => $this->viewingRequest,
                'brand' => $this->viewingRequest?->brand,
                'impact' => $this->viewingRequestImpact,
                'assignedCategories' => $this->viewingRequest?->brand?->categories ?? collect(),
                'imageUrl' => BrandImageStorage::url($this->viewingRequest?->brand?->logo_path),
            ]))
            ->modalFooterActions([
                Action::make('closeViewBrandDeletionRequest')
                    ->label('Close')
                    ->color('gray')
                    ->close(),
                Action::make('viewBrandFromDeletionRequest')
                    ->label('View Brand')
                    ->visible(fn (): bool => (bool) $this->viewingRequest?->brand_id)
                    ->action(function (): void {
                        $requestId = $this->viewingRequestId;
                        $this->unmountAction(false);

                        if ($requestId) {
                            $this->openViewBrandFromRequest($requestId);
                        }
                    }),
                Action::make('returnFromImpactModal')
                    ->label('Return for Correction')
                    ->color('gray')
                    ->visible(fn (): bool => $this->canPageAction(PermissionAction::Approve) && (bool) $this->viewingRequestId)
                    ->action(function (): void {
                        $requestId = $this->viewingRequestId;
                        $this->unmountAction(false);
                        $this->openReturnDeletionModal((int) $requestId);
                    }),
                Action::make('rejectFromImpactModal')
                    ->label('Reject Delete')
                    ->color('warning')
                    ->visible(fn (): bool => $this->canPageAction(PermissionAction::Approve) && (bool) $this->viewingRequestId)
                    ->action(function (): void {
                        $requestId = $this->viewingRequestId;
                        $this->unmountAction(false);
                        $this->openRejectDeletionModal((int) $requestId);
                    }),
                Action::make('approveFromImpactModal')
                    ->label('Approve Delete')
                    ->color('danger')
                    ->visible(fn (): bool => $this->canPageAction(PermissionAction::Approve)
                        && (bool) $this->viewingRequestImpact?->canApprove)
                    ->requiresConfirmation()
                    ->modalHeading('Approve brand deletion?')
                    ->modalDescription('This will soft-delete the brand. Assigned categories and products are not removed automatically.')
                    ->action(function (): void {
                        if ($this->viewingRequestId) {
                            $this->approveDeletionRequest($this->viewingRequestId);
                            $this->unmountAction(false);
                        }
                    }),
            ]);
    }

    public function openReturnDeletionModal(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->returningRequestId = $requestId;
        $this->mountAction('returnBrandDeletion');
    }

    public function returnBrandDeletionAction(): Action
    {
        return Action::make('returnBrandDeletion')
            ->label('Return for Correction')
            ->color('warning')
            ->modalHeading('Return Brand Deletion Request')
            ->modalDescription('The brand will be restored. The requester can review your notes and submit a new deletion request if needed.')
            ->modalWidth(Width::Medium)
            ->modalSubmitActionLabel('Return for Correction')
            ->schema([
                Textarea::make('returnNotes')
                    ->label('Return Reason')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->action(function (array $data): void {
                if ($this->returningRequestId) {
                    $this->returnDeletionRequest($this->returningRequestId, (string) ($data['returnNotes'] ?? ''));
                }
            });
    }

    public function openRejectDeletionModal(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->reviewingRequestId = $requestId;
        $this->mountAction('rejectBrandDeletion');
    }

    public function rejectBrandDeletionAction(): Action
    {
        return Action::make('rejectBrandDeletion')
            ->label('Reject Delete')
            ->color('warning')
            ->modalHeading('Reject Brand Deletion')
            ->modalWidth(Width::Medium)
            ->modalSubmitActionLabel('Reject Delete')
            ->schema([
                Textarea::make('reviewNotes')
                    ->label('Rejection Reason')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->action(function (array $data): void {
                if ($this->reviewingRequestId) {
                    $this->rejectDeletionRequest($this->reviewingRequestId, (string) ($data['reviewNotes'] ?? ''));
                }
            });
    }

    public function approveDeletionRequest(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        try {
            $request = BrandDeletionRequest::query()->with('brand')->findOrFail($requestId);
            $brand = BrandDeletionService::approveDeletion($request, auth()->user());

            $this->finalizeDeletionReview(
                title: 'Deletion approved',
                body: "{$brand->code} has been soft-deleted.",
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Approval failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function rejectDeletionRequest(int $requestId, string $reviewNotes): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        try {
            $request = BrandDeletionRequest::query()->with('brand')->findOrFail($requestId);
            $brand = BrandDeletionService::rejectDeletion($request, auth()->user(), $reviewNotes);

            $this->finalizeDeletionReview(
                title: 'Deletion request rejected',
                body: "{$brand->code} has been restored to active catalog status.",
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Rejection failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function returnDeletionRequest(int $requestId, string $returnNotes): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        try {
            $request = BrandDeletionRequest::query()->with('brand')->findOrFail($requestId);
            $brand = BrandDeletionService::returnDeletion($request, auth()->user(), $returnNotes);

            $this->finalizeDeletionReview(
                title: 'Deletion request returned',
                body: "{$brand->code} has been returned for correction. The requester can review your notes and resubmit if needed.",
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Return failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    protected function finalizeDeletionReview(?string $title = null, ?string $body = null): void
    {
        $this->viewingRequestId = null;
        $this->reviewingRequestId = null;
        $this->returningRequestId = null;
        $this->viewingBrandId = null;

        unset(
            $this->pendingDeletionRequests,
            $this->viewingRequest,
            $this->viewingRequestImpact,
            $this->viewingBrand,
            $this->viewingBrandAssignedCategories,
            $this->viewingBrandImageDetails,
            $this->viewingBrandPromptInfo,
        );

        for ($attempt = 0; $attempt < 5 && filled($this->getMountedAction()?->getName()); $attempt++) {
            $this->unmountAction(false);
        }

        if ($title !== null) {
            Notification::make()
                ->title($title)
                ->success()
                ->body($body)
                ->send();
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.approvals.brand-delete-requests-page'),
        ]);
    }
}
