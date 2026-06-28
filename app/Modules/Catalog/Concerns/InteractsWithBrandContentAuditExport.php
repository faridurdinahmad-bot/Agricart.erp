<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Core\ContentAudit\Enums\ContentAuditFormat;
use App\Core\ContentAudit\Services\ContentAuditExportService;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\ContentAudit\BrandContentAuditBuilder;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait InteractsWithBrandContentAuditExport
{
    public ?int $contentAuditBrandId = null;

    public function promptExportBrandReview(int $brandId): void
    {
        $this->authorizePageAction(PermissionAction::Export);
        $this->contentAuditBrandId = $brandId;
        $this->mountAction('exportBrandReview');
    }

    public function exportBrandReviewAction(): Action
    {
        return Action::make('exportBrandReview')
            ->modalHeading('Export for Review')
            ->modalDescription('Print the brand review from the view page, or download an audit PDF / JSON for external tools. No sensitive credentials are included.')
            ->modalWidth(Width::Large)
            ->modalContent(fn (): View => view('filament.catalog.partials.brand-export-review-picker'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function printBrandReviewFromExport(): void
    {
        $this->printBrandReview(openViewIfNeeded: true);
    }

    public function printBrandRowReview(int $brandId): void
    {
        $this->contentAuditBrandId = $brandId;
        $this->printBrandReview(openViewIfNeeded: true);
    }

    public function printBrandReview(bool $openViewIfNeeded = false): void
    {
        $this->authorizePageAction(PermissionAction::Print);

        $brandId = (int) ($this->viewingBrandId ?? $this->contentAuditBrandId ?? $this->editingBrandId ?? 0);

        if ($brandId <= 0) {
            Notification::make()
                ->title('No brand selected for print')
                ->warning()
                ->send();

            return;
        }

        $delay = 0;
        $viewIsOpen = $this->getMountedAction()?->getName() === 'viewBrand';

        if ($openViewIfNeeded && ! $viewIsOpen) {
            $this->viewingBrandId = $brandId;
            $this->contentAuditBrandId = $brandId;
            $this->unmountAction(false);
            $this->mountAction('viewBrand');
            $delay = 450;
        } elseif (! $this->viewingBrandId) {
            $this->viewingBrandId = $brandId;
            $this->contentAuditBrandId = $brandId;
        }

        $this->js('window.AgricartPrint?.review('.max(0, $delay).')');
    }

    public function downloadBrandContentAudit(string $format): ?StreamedResponse
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

        $brandId = $this->contentAuditBrandId ?? $this->editingBrandId;

        if (! $brandId) {
            Notification::make()
                ->title('No brand selected for export')
                ->warning()
                ->send();

            return null;
        }

        $brand = Brand::query()->findOrFail($brandId);

        return app(ContentAuditExportService::class)->downloadFromBuilder(
            app(BrandContentAuditBuilder::class),
            $brand,
            $auditFormat,
        );
    }

    protected function exportBrandReviewFooterAction(): Action
    {
        return Action::make('exportBrandForReview')
            ->label('Export for Review')
            ->outlined()
            ->visible(fn (): bool => (bool) $this->editingBrandId && $this->canPageAction(PermissionAction::Export))
            ->action(function (): void {
                $this->authorizePageAction(PermissionAction::Export);
                $this->contentAuditBrandId = $this->editingBrandId;
                $this->mountAction('exportBrandReview');
                $this->halt();
            });
    }
}
