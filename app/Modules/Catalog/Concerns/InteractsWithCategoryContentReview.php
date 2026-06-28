<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Catalog\Category;
use App\Modules\Catalog\Services\CategoryManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

trait InteractsWithCategoryContentReview
{
    public function approveCategoryContentReview(int $categoryId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        $category = Category::query()->findOrFail($categoryId);

        if (! $category->awaitingContentReview()) {
            Notification::make()
                ->title('Review not required')
                ->body('This category is not awaiting content review.')
                ->warning()
                ->send();

            return;
        }

        CategoryManager::approveContentReview($category, auth()->user());

        unset(
            $this->categoryTree,
            $this->flatCategories,
            $this->categoryParentOptions,
            $this->filteredCategories,
            $this->paginatedCategories,
            $this->categoryListMeta,
        );

        Notification::make()
            ->title('Content marked as reviewed')
            ->body("{$category->code} is now marked Complete.")
            ->success()
            ->send();
    }

    protected function markCategoryReviewedFooterAction(): Action
    {
        return Action::make('markCategoryReviewed')
            ->label('Mark as Reviewed')
            ->color('success')
            ->visible(fn (): bool => (bool) $this->viewingCategory?->awaitingContentReview())
            ->requiresConfirmation()
            ->modalHeading('Mark content as reviewed?')
            ->modalDescription('This confirms the category AI content has been reviewed and approved. Status will change from Needs Review to Complete.')
            ->action(function (): void {
                if (! $this->viewingCategoryId) {
                    return;
                }

                $this->approveCategoryContentReview($this->viewingCategoryId);
                $this->unmountAction(false);
            });
    }
}
