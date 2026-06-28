<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\ContentAudit\Support\PromptAuditMetadataResolver;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\ContentAudit\Support\BrandImageMetadataResolver;
use App\Modules\Catalog\Services\BrandImageStorage;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

trait InteractsWithBrandView
{
    public ?int $viewingBrandId = null;

    public function openViewBrand(int $brandId): void
    {
        $this->authorizePageAction(PermissionAction::View);
        $this->viewingBrandId = $brandId;
        $this->mountAction('viewBrand');
    }

    public function viewBrandAction(): Action
    {
        return Action::make('viewBrand')
            ->modalHeading(fn (): string => 'View Brand — '.($this->viewingBrand?->code ?? ''))
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): View => view('filament.catalog.brand-view', [
                'brand' => $this->viewingBrand,
                'assignedCategories' => $this->viewingBrandAssignedCategories,
                'imageUrl' => BrandImageStorage::url($this->viewingBrand?->logo_path),
                'imageDetails' => $this->viewingBrandImageDetails,
                'promptInfo' => $this->viewingBrandPromptInfo,
            ]))
            ->modalFooterActions($this->brandViewModalFooterActions());
    }

    /**
     * @return list<Action>
     */
    protected function brandViewModalFooterActions(): array
    {
        $actions = [
            Action::make('closeViewBrand')
                ->label('Close')
                ->color('gray')
                ->close(),
        ];

        if (method_exists($this, 'markBrandReviewedFooterAction')) {
            $actions[] = $this->markBrandReviewedFooterAction();
        }

        if (method_exists($this, 'openEditBrand')) {
            $actions[] = Action::make('viewBrandEdit')
                ->label('Edit')
                ->visible(fn (): bool => (bool) $this->viewingBrandId
                    && $this->canPageAction(PermissionAction::Update)
                    && ! ($this->viewingBrand?->isPendingDeletion() ?? false))
                ->action(function (): void {
                    $brandId = $this->viewingBrandId;
                    $this->unmountAction();
                    $this->openEditBrand((int) $brandId);
                });
        }

        if (method_exists($this, 'printBrandReview')) {
            $actions[] = Action::make('viewBrandPrint')
                ->label('Print Review (Recommended)')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->extraAttributes(['class' => 'agricart-no-print'])
                ->visible(fn (): bool => (bool) $this->viewingBrandId && $this->canPageAction(PermissionAction::Print))
                ->action(fn (): mixed => $this->printBrandReview());
        }

        if (method_exists($this, 'promptExportBrandReview')) {
            $actions[] = Action::make('viewBrandExport')
                ->label('Export for Review')
                ->outlined()
                ->extraAttributes(['class' => 'agricart-no-print'])
                ->visible(fn (): bool => (bool) $this->viewingBrandId && $this->canPageAction(PermissionAction::Export))
                ->action(function (): void {
                    $this->contentAuditBrandId = $this->viewingBrandId;
                    $this->mountAction('exportBrandReview');
                    $this->halt();
                });
        }

        return $actions;
    }

    public function getViewingBrandProperty(): ?Brand
    {
        if (! $this->viewingBrandId) {
            return null;
        }

        return Brand::query()->with('categories')->find($this->viewingBrandId);
    }

    /**
     * @return list<array{code: string, name_en: string}>
     */
    public function getViewingBrandAssignedCategoriesProperty(): array
    {
        return $this->viewingBrand?->categories
            ->map(fn ($category): array => [
                'code' => $category->code,
                'name_en' => $category->name_en,
            ])
            ->all() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewingBrandImageDetailsProperty(): array
    {
        return BrandImageMetadataResolver::resolve($this->viewingBrand?->logo_path);
    }

    /**
     * @return array<string, string|null>
     */
    public function getViewingBrandPromptInfoProperty(): array
    {
        return PromptAuditMetadataResolver::resolve(
            AiTaskType::BrandContent,
            AiTargetModule::Catalog,
        );
    }
}
