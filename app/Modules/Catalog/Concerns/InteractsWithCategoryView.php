<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\ContentAudit\Support\PromptAuditMetadataResolver;
use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryUrlRedirect;
use App\Modules\Catalog\ContentAudit\Support\CategoryImageMetadataResolver;
use App\Modules\Catalog\Services\CategoryImageStorage;
use App\Modules\Catalog\Services\CategoryManager;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

trait InteractsWithCategoryView
{
    public ?int $viewingCategoryId = null;

    public function openViewCategory(int $categoryId): void
    {
        $this->authorizePageAction(PermissionAction::View);
        $this->viewingCategoryId = $categoryId;
        $this->mountAction('viewCategory');
    }

    public function viewCategoryAction(): Action
    {
        return Action::make('viewCategory')
            ->modalHeading(fn (): string => 'View Category — '.($this->viewingCategory?->code ?? ''))
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): View => view('filament.catalog.category-view', [
                'category' => $this->viewingCategory,
                'hierarchy' => $this->viewingCategoryHierarchy,
                'imageUrl' => CategoryImageStorage::url($this->viewingCategory?->image_path),
                'imageDetails' => $this->viewingCategoryImageDetails,
                'promptInfo' => $this->viewingCategoryPromptInfo,
                'urlRedirects' => $this->viewingCategoryUrlRedirects,
            ]))
            ->modalFooterActions([
                Action::make('closeViewCategory')
                    ->label('Close')
                    ->color('gray')
                    ->close(),
                $this->markCategoryReviewedFooterAction(),
                Action::make('viewCategoryEdit')
                    ->label('Edit')
                    ->visible(fn (): bool => (bool) $this->viewingCategoryId)
                    ->action(function (): void {
                        $categoryId = $this->viewingCategoryId;
                        $this->unmountAction();
                        $this->openEditCategory((int) $categoryId);
                    }),
                Action::make('viewCategoryPrint')
                    ->label('Print Review (Recommended)')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->extraAttributes(['class' => 'agricart-no-print'])
                    ->visible(fn (): bool => (bool) $this->viewingCategoryId)
                    ->action(fn (): mixed => $this->printCategoryReview()),
                Action::make('viewCategoryExport')
                    ->label('Export for Review')
                    ->outlined()
                    ->extraAttributes(['class' => 'agricart-no-print'])
                    ->visible(fn (): bool => (bool) $this->viewingCategoryId)
                    ->action(function (): void {
                        $this->contentAuditCategoryId = $this->viewingCategoryId;
                        $this->mountAction('exportCategoryReview');
                        $this->halt();
                    }),
            ]);
    }

    public function getViewingCategoryProperty(): ?Category
    {
        if (! $this->viewingCategoryId) {
            return null;
        }

        return Category::query()->with(['urlRedirects.changedByUser'])->find($this->viewingCategoryId);
    }

    /**
     * @return Collection<int, CategoryUrlRedirect>
     */
    public function getViewingCategoryUrlRedirectsProperty(): Collection
    {
        return $this->viewingCategory?->urlRedirects ?? collect();
    }

    /**
     * @return list<array{level: int, code: string, english_name: string, urdu_name: string|null, is_current: bool}>
     */
    public function getViewingCategoryHierarchyProperty(): array
    {
        if (! $this->viewingCategory) {
            return [];
        }

        return CategoryManager::hierarchyChain($this->viewingCategory);
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewingCategoryImageDetailsProperty(): array
    {
        return CategoryImageMetadataResolver::resolve($this->viewingCategory?->image_path);
    }

    /**
     * @return array<string, string|null>
     */
    public function getViewingCategoryPromptInfoProperty(): array
    {
        return PromptAuditMetadataResolver::resolve(
            AiTaskType::CategoryContent,
            AiTargetModule::Catalog,
        );
    }
}
