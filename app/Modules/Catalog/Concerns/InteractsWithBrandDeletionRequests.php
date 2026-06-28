<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\Services\BrandManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;

trait InteractsWithBrandDeletionRequests
{
    public ?int $requestingDeletionBrandId = null;

    public function openRequestDeletionModal(int $brandId): void
    {
        $this->authorizePageAction(PermissionAction::Delete);

        $brand = Brand::query()->findOrFail($brandId);

        if ($brand->isPendingDeletion()) {
            Notification::make()
                ->title('Deletion already requested')
                ->body('This brand is already pending deletion approval. Go to Approvals → Brand Delete Requests.')
                ->warning()
                ->send();

            return;
        }

        $this->requestingDeletionBrandId = $brandId;
        $this->mountAction('requestBrandDeletion');
    }

    public function requestBrandDeletionAction(): Action
    {
        return Action::make('requestBrandDeletion')
            ->label('Request Deletion')
            ->color('danger')
            ->modalHeading('Request Brand Deletion')
            ->modalDescription('The brand will be marked Pending Deletion and sent to Approvals → Brand Delete Requests. Nothing is removed until approved. A reason is required.')
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
                $this->submitBrandDeletionRequest((string) ($data['deletionReason'] ?? ''));
            });
    }

    public function submitBrandDeletionRequest(string $reason): void
    {
        if (! $this->requestingDeletionBrandId) {
            return;
        }

        $this->authorizePageAction(PermissionAction::Delete);

        try {
            $brand = Brand::query()->findOrFail($this->requestingDeletionBrandId);

            BrandManager::requestDeletion($brand, auth()->user(), $reason);

            $this->refreshBrandListComputed();

            $this->requestingDeletionBrandId = null;

            $this->completeModalSave(
                title: 'Deletion request submitted',
                body: "{$brand->code} is now Pending Deletion. An approver can review it under Approvals → Brand Delete Requests.",
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
