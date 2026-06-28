<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Catalog\Category;
use App\Modules\Catalog\Services\CategoryManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;

trait InteractsWithCategoryDeletionRequests
{
    public ?int $requestingDeletionCategoryId = null;

    public function openRequestDeletionModal(int $categoryId): void
    {
        $this->authorizePageAction(PermissionAction::Delete);

        $category = Category::query()->findOrFail($categoryId);

        if ($category->isPendingDeletion()) {
            Notification::make()
                ->title('Deletion already requested')
                ->body('This category is already pending deletion approval. Go to Approvals → Category Delete Requests.')
                ->warning()
                ->send();

            return;
        }

        $this->requestingDeletionCategoryId = $categoryId;
        $this->mountAction('requestCategoryDeletion');
    }

    public function requestCategoryDeletionAction(): Action
    {
        return Action::make('requestCategoryDeletion')
            ->label('Request Deletion')
            ->color('danger')
            ->modalHeading('Request Category Deletion')
            ->modalDescription('The category will be marked Pending Deletion and sent to Approvals → Category Delete Requests. Nothing is removed until approved. A reason is required.')
            ->modalWidth(Width::Medium)
            ->modalSubmitActionLabel('Submit Deletion Request')
            ->schema([
                Textarea::make('deletionReason')
                    ->label('Deletion Reason')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->action(function (array $data): void {
                $this->submitCategoryDeletionRequest((string) ($data['deletionReason'] ?? ''));
            });
    }

    public function submitCategoryDeletionRequest(string $reason): void
    {
        if (! $this->requestingDeletionCategoryId) {
            return;
        }

        $this->authorizePageAction(PermissionAction::Delete);

        try {
            $category = Category::query()->findOrFail($this->requestingDeletionCategoryId);

            CategoryManager::requestDeletion($category, auth()->user(), $reason);

            unset(
                $this->categoryTree,
                $this->flatCategories,
                $this->categoryParentOptions,
                $this->filteredCategories,
                $this->paginatedCategories,
                $this->categoryListMeta,
            );

            $this->requestingDeletionCategoryId = null;

            $this->completeModalSave(
                title: 'Deletion request submitted',
                body: "{$category->code} is now Pending Deletion. An approver can review it under Approvals → Category Delete Requests.",
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Deletion request failed')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }
}
