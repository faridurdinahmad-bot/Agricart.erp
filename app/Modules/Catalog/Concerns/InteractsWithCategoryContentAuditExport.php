<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Core\ContentAudit\Enums\ContentAuditFormat;
use App\Core\ContentAudit\Services\ContentAuditExportService;
use App\Models\Catalog\Category;
use App\Modules\Catalog\ContentAudit\CategoryContentAuditBuilder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait InteractsWithCategoryContentAuditExport
{
    public ?int $contentAuditCategoryId = null;

    public function promptExportCategoryReview(int $categoryId): void
    {
        $this->authorizePageAction(PermissionAction::Export);
        $this->contentAuditCategoryId = $categoryId;
        $this->mountAction('exportCategoryReview');
    }

    public function exportCategoryReviewAction(): Action
    {
        return Action::make('exportCategoryReview')
            ->modalHeading('Export for Review')
            ->modalDescription('Print the category review from the view page, or download an audit PDF / JSON for external tools. No sensitive credentials are included.')
            ->modalWidth(Width::Large)
            ->modalContent(fn (): View => view('filament.catalog.partials.category-export-review-picker'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function printCategoryReviewFromExport(): void
    {
        $this->printCategoryReview(openViewIfNeeded: true);
    }

    public function printCategoryReview(bool $openViewIfNeeded = false): void
    {
        $this->authorizePageAction(PermissionAction::Export);

        $categoryId = (int) ($this->viewingCategoryId ?? $this->contentAuditCategoryId ?? $this->editingCategoryId ?? 0);

        if ($categoryId <= 0) {
            Notification::make()
                ->title('No category selected for print')
                ->warning()
                ->send();

            return;
        }

        $delay = 0;
        $viewIsOpen = $this->getMountedAction()?->getName() === 'viewCategory';

        if ($openViewIfNeeded && ! $viewIsOpen) {
            $this->viewingCategoryId = $categoryId;
            $this->contentAuditCategoryId = $categoryId;
            $this->unmountAction(false);
            $this->mountAction('viewCategory');
            $delay = 450;
        } elseif (! $this->viewingCategoryId) {
            $this->viewingCategoryId = $categoryId;
            $this->contentAuditCategoryId = $categoryId;
        }

        $this->js('window.AgricartPrint?.review('.max(0, $delay).')');
    }

    public function downloadCategoryContentAudit(string $format): ?StreamedResponse
    {
        $this->authorizePageAction(PermissionAction::Export);

        $auditFormat = ContentAuditFormat::tryFromInput($format);

        if (! $auditFormat) {
            Notification::make()
                ->title('Unsupported export format')
                ->warning()
                ->send();

            return null;
        }

        $categoryId = $this->contentAuditCategoryId ?? $this->editingCategoryId;

        if (! $categoryId) {
            Notification::make()
                ->title('No category selected for export')
                ->warning()
                ->send();

            return null;
        }

        $category = Category::query()->findOrFail($categoryId);

        return app(ContentAuditExportService::class)->downloadFromBuilder(
            app(CategoryContentAuditBuilder::class),
            $category,
            $auditFormat,
        );
    }

    protected function exportCategoryReviewFooterAction(): Action
    {
        return Action::make('exportCategoryForReview')
            ->label('Export for Review')
            ->outlined()
            ->visible(fn (): bool => (bool) $this->editingCategoryId)
            ->action(function (): void {
                $this->authorizePageAction(PermissionAction::Export);
                $this->contentAuditCategoryId = $this->editingCategoryId;
                $this->mountAction('exportCategoryReview');
                $this->halt();
            });
    }
}
