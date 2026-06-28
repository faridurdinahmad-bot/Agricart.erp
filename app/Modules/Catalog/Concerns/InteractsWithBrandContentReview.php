<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\Services\BrandManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

trait InteractsWithBrandContentReview
{
    public function approveBrandContentReview(int $brandId): void
    {
        $this->authorizePageAction(PermissionAction::Approve);

        $brand = Brand::query()->findOrFail($brandId);

        if (! $brand->awaitingContentReview()) {
            Notification::make()
                ->title('Review not required')
                ->body('This brand is not awaiting content review.')
                ->warning()
                ->send();

            return;
        }

        BrandManager::approveContentReview($brand, auth()->user());

        $this->refreshBrandListComputed();

        Notification::make()
            ->title('Content marked as reviewed')
            ->body("{$brand->code} is now marked Complete.")
            ->success()
            ->send();
    }

    protected function markBrandReviewedFooterAction(): Action
    {
        return Action::make('markBrandReviewed')
            ->label('Mark as Reviewed')
            ->color('success')
            ->visible(fn (): bool => (bool) $this->viewingBrand?->awaitingContentReview()
                && $this->canPageAction(PermissionAction::Approve))
            ->requiresConfirmation()
            ->modalHeading('Mark content as reviewed?')
            ->modalDescription('This confirms the brand AI content has been reviewed and approved. Status will change from Needs Review to Complete.')
            ->action(function (): void {
                if (! $this->viewingBrandId) {
                    return;
                }

                $this->approveBrandContentReview($this->viewingBrandId);
                $this->unmountAction(false);
            });
    }
}
