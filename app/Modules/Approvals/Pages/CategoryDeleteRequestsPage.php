<?php

namespace App\Modules\Approvals\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Catalog\CategoryDeletionRequest;
use App\Modules\Approvals\Clusters\ApprovalsCluster;
use App\Modules\Approvals\Navigation\ApprovalsNavigation;
use App\Modules\Catalog\Concerns\InteractsWithCategoryView;
use App\Modules\Catalog\Dto\CategoryDeletionImpact;
use App\Modules\Catalog\Services\CategoryDeletionImpactAnalyzer;
use App\Modules\Catalog\Services\CategoryDeletionService;
use App\Modules\Catalog\Services\CategoryImageStorage;
use App\Modules\Catalog\Services\CategoryManager;
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

class CategoryDeleteRequestsPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal, InteractsWithCategoryView;

    protected static ?string $cluster = ApprovalsCluster::class;

    protected static ?string $navigationLabel = 'Category Delete Requests';

    protected static ?string $title = 'Category Delete Requests';

    protected static ?string $slug = 'category-delete-requests';

    protected static ?int $navigationSort = ApprovalsNavigation::CATEGORY_DELETE_REQUESTS;

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
        return ['agricart-approvals-page', 'agricart-category-delete-requests-page'];
    }

    #[Computed]
    public function pendingDeletionRequests()
    {
        return CategoryDeletionRequest::query()
            ->with(['category', 'requestedByUser'])
            ->where('status', EntityDeletionRequestStatus::Pending)
            ->orderByDesc('requested_at')
            ->get();
    }

    #[Computed]
    public function viewingRequest(): ?CategoryDeletionRequest
    {
        if (! $this->viewingRequestId) {
            return null;
        }

        return CategoryDeletionRequest::query()
            ->with(['category', 'requestedByUser'])
            ->find($this->viewingRequestId);
    }

    #[Computed]
    public function viewingRequestImpact(): ?CategoryDeletionImpact
    {
        if (! $this->viewingRequest?->category) {
            return null;
        }

        return CategoryDeletionImpactAnalyzer::analyze($this->viewingRequest->category);
    }

    public function openViewDeletionRequest(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);
        $this->viewingRequestId = $requestId;
        unset($this->viewingRequest, $this->viewingRequestImpact);
        $this->mountAction('viewCategoryDeletionRequest');
    }

    public function openViewCategoryFromRequest(int $requestId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        $request = CategoryDeletionRequest::query()->findOrFail($requestId);

        if (! $request->category_id) {
            return;
        }

        $this->viewingCategoryId = $request->category_id;
        unset($this->viewingCategory, $this->viewingCategoryHierarchy, $this->viewingCategoryImageDetails, $this->viewingCategoryPromptInfo, $this->viewingCategoryUrlRedirects);
        $this->mountAction('viewCategory');
    }

    public function viewCategoryDeletionRequestAction(): Action
    {
        return Action::make('viewCategoryDeletionRequest')
            ->modalHeading(fn (): string => 'Deletion Impact — '.($this->viewingRequest?->category?->code ?? ''))
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.approvals.category-deletion-impact', [
                'request' => $this->viewingRequest,
                'category' => $this->viewingRequest?->category,
                'impact' => $this->viewingRequestImpact,
                'hierarchy' => $this->viewingRequest?->category
                    ? CategoryManager::hierarchyChain($this->viewingRequest->category)
                    : [],
                'imageUrl' => CategoryImageStorage::url($this->viewingRequest?->category?->image_path),
            ]))
            ->modalFooterActions([
                Action::make('closeViewCategoryDeletionRequest')
                    ->label('Close')
                    ->color('gray')
                    ->close(),
                Action::make('viewCategoryFromDeletionRequest')
                    ->label('View Category')
                    ->visible(fn (): bool => (bool) $this->viewingRequest?->category_id)
                    ->action(function (): void {
                        $requestId = $this->viewingRequestId;
                        $this->unmountAction(false);

                        if ($requestId) {
                            $this->openViewCategoryFromRequest($requestId);
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
                    ->modalHeading('Approve category deletion?')
                    ->modalDescription('This will soft-delete the category. Children, products, and files are not removed automatically.')
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
        $this->mountAction('returnCategoryDeletion');
    }

    public function returnCategoryDeletionAction(): Action
    {
        return Action::make('returnCategoryDeletion')
            ->label('Return for Correction')
            ->color('warning')
            ->modalHeading('Return Category Deletion Request')
            ->modalDescription('The category will be restored. The requester can review your notes and submit a new deletion request if needed.')
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
        $this->mountAction('rejectCategoryDeletion');
    }

    public function rejectCategoryDeletionAction(): Action
    {
        return Action::make('rejectCategoryDeletion')
            ->label('Reject Delete')
            ->color('warning')
            ->modalHeading('Reject Category Deletion')
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
            $request = CategoryDeletionRequest::query()->with('category')->findOrFail($requestId);
            $category = CategoryDeletionService::approveDeletion($request, auth()->user());

            $this->finalizeDeletionReview(
                title: 'Deletion approved',
                body: "{$category->code} has been soft-deleted.",
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
            $request = CategoryDeletionRequest::query()->with('category')->findOrFail($requestId);
            $category = CategoryDeletionService::rejectDeletion($request, auth()->user(), $reviewNotes);

            $this->finalizeDeletionReview(
                title: 'Deletion request rejected',
                body: "{$category->code} has been restored to active catalog status.",
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
            $request = CategoryDeletionRequest::query()->with('category')->findOrFail($requestId);
            $category = CategoryDeletionService::returnDeletion($request, auth()->user(), $returnNotes);

            $this->finalizeDeletionReview(
                title: 'Deletion request returned',
                body: "{$category->code} has been returned for correction. The requester can review your notes and resubmit if needed.",
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
        $this->viewingCategoryId = null;

        unset(
            $this->pendingDeletionRequests,
            $this->viewingRequest,
            $this->viewingRequestImpact,
            $this->viewingCategory,
            $this->viewingCategoryHierarchy,
            $this->viewingCategoryImageDetails,
            $this->viewingCategoryPromptInfo,
            $this->viewingCategoryUrlRedirects,
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
        return $schema
            ->components([
                View::make('filament.approvals.category-delete-requests-page'),
            ]);
    }
}
