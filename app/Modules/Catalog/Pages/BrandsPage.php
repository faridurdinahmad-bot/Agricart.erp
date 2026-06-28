<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Concerns\InteractsWithBrandContentAuditExport;
use App\Modules\Catalog\Concerns\InteractsWithBrandContentReview;
use App\Modules\Catalog\Concerns\InteractsWithBrandDeletionRequests;
use App\Modules\Catalog\Concerns\InteractsWithBrandForm;
use App\Modules\Catalog\Concerns\InteractsWithBrandView;
use App\Modules\Catalog\Enums\BrandLifecycleStatus;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use App\Modules\Catalog\Services\BrandManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class BrandsPage extends BaseModulePage
{
    use AuthorizesPageActions,
        HandlesCrudModal,
        InteractsWithBrandContentAuditExport,
        InteractsWithBrandContentReview,
        InteractsWithBrandDeletionRequests,
        InteractsWithBrandForm,
        InteractsWithBrandView;

    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Brands';

    protected static ?string $title = 'Brands';

    protected static ?string $slug = 'brands';

    protected static ?int $navigationSort = CatalogNavigation::BRANDS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public string $search = '';

    public string $statusFilter = '';

    public string $aiStatusFilter = '';

    public int $brandsPage = 1;

    public int $brandsPerPage = 25;

    public function mount(): void
    {
        $this->resetBrandForm();
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-brands-page'];
    }

    #[Computed]
    public function filteredBrands()
    {
        $query = Brand::query()
            ->with('categories')
            ->orderBy('name_en');

        if ($this->search !== '') {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($builder) use ($term): void {
                $builder->whereRaw('LOWER(name_en) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_ur) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(code) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(short_note) LIKE ?', [$term]);
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true)
                ->where('lifecycle_status', BrandLifecycleStatus::Active);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false)
                ->where('lifecycle_status', BrandLifecycleStatus::Active);
        } elseif ($this->statusFilter === 'pending_deletion') {
            $query->where('lifecycle_status', BrandLifecycleStatus::PendingDeletion);
        }

        if ($this->aiStatusFilter === 'needs_attention') {
            $query->needsAiAttention();
        } elseif ($this->aiStatusFilter !== '') {
            $query->where('ai_content_status', $this->aiStatusFilter);
        }

        return $query->get();
    }

    #[Computed]
    public function paginatedBrands()
    {
        $offset = ($this->brandsPage - 1) * $this->brandsPerPage;

        return $this->filteredBrands->slice($offset, $this->brandsPerPage)->values();
    }

    /**
     * @return array{total: int, from: int, to: int, currentPage: int, lastPage: int}
     */
    #[Computed]
    public function brandListMeta(): array
    {
        $total = $this->filteredBrands->count();
        $lastPage = max(1, (int) ceil($total / $this->brandsPerPage));
        $currentPage = min($this->brandsPage, $lastPage);
        $from = $total === 0 ? 0 : (($currentPage - 1) * $this->brandsPerPage) + 1;
        $to = min($total, $currentPage * $this->brandsPerPage);

        return [
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function updatedSearch(): void
    {
        $this->brandsPage = 1;
        unset($this->filteredBrands, $this->paginatedBrands, $this->brandListMeta);
    }

    public function updatedStatusFilter(): void
    {
        $this->brandsPage = 1;
        unset($this->filteredBrands, $this->paginatedBrands, $this->brandListMeta);
    }

    public function updatedAiStatusFilter(): void
    {
        $this->brandsPage = 1;
        unset($this->filteredBrands, $this->paginatedBrands, $this->brandListMeta);
    }

    public function goToBrandsPage(int $page): void
    {
        $lastPage = $this->brandListMeta['lastPage'];
        $this->brandsPage = max(1, min($page, $lastPage));
        unset($this->paginatedBrands, $this->brandListMeta);
    }

    public function addBrandAction(): Action
    {
        return $this->configureBrandFormAction(
            Action::make('addBrand')
                ->label('Add Brand')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->visible(fn (): bool => $this->canPageAction(PermissionAction::Create))
                ->before(function (): void {
                    $this->resetBrandForm();
                }),
        );
    }

    public function brandFormAction(): Action
    {
        return $this->configureBrandFormAction(Action::make('brandForm'));
    }

    public function openEditBrand(int $brandId): void
    {
        $this->authorizePageAction(PermissionAction::Update);

        $brand = Brand::query()->findOrFail($brandId);

        if ($brand->isPendingDeletion()) {
            Notification::make()
                ->title('Brand pending deletion')
                ->body('This brand cannot be edited while a deletion request is pending approval.')
                ->warning()
                ->send();

            return;
        }

        $this->loadBrandForEdit($brandId);
        $this->mountAction('brandForm');
    }

    protected function configureBrandFormAction(Action $action): Action
    {
        return $action
            ->modalHeading(fn (): string => $this->editingBrandId ? 'Edit Brand' : 'Add Brand')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.catalog.brand-form', [
                'live' => true,
            ]))
            ->stickyModalFooter()
            ->modalFooterActions([
                Action::make('cancelBrandForm')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                $this->exportBrandReviewFooterAction(),
                Action::make('saveAndAddNextBrand')
                    ->label('Save & Add Next')
                    ->outlined()
                    ->visible(fn (): bool => ! $this->editingBrandId && $this->canPageAction(PermissionAction::Create))
                    ->action(function (): void {
                        $this->saveBrand(addAnother: true);
                    }),
                Action::make('submitBrandForm')
                    ->label('Save & Close')
                    ->color('primary')
                    ->visible(fn (): bool => $this->editingBrandId
                        ? $this->canPageAction(PermissionAction::Update)
                        : $this->canPageAction(PermissionAction::Create))
                    ->cancelParentActions()
                    ->action(function (): void {
                        $this->saveBrand();
                    }),
            ]);
    }

    public function saveBrand(bool $addAnother = false): void
    {
        $this->authorizePageAction(
            $this->editingBrandId ? PermissionAction::Update : PermissionAction::Create,
        );

        try {
            $payload = $this->validateBrandForm();
            $categoryIds = $payload['category_ids'] ?? [];
            unset($payload['category_ids']);
            $logo = $this->brandLogoUpload();

            if ($this->editingBrandId) {
                $brand = Brand::query()->findOrFail($this->editingBrandId);
                BrandManager::update($brand, $payload, $categoryIds, $logo);
                $message = 'Brand updated successfully.';
            } else {
                BrandManager::create($payload, $categoryIds, $logo);
                $message = 'Brand created successfully.';
            }

            $this->refreshBrandListComputed();
            $this->resetBrandForm();

            if ($addAnother) {
                Notification::make()->title($message)->success()->send();
                $this->replaceMountedAction('addBrand');

                return;
            }

            $this->completeModalSave(
                addAnother: false,
                title: $message,
                refreshNavigation: true,
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    public function duplicateBrand(int $brandId): void
    {
        $this->authorizePageAction(PermissionAction::Create);

        $source = Brand::query()->with('categories')->findOrFail($brandId);

        if ($source->isPendingDeletion()) {
            Notification::make()
                ->title('Brand pending deletion')
                ->body('Cannot duplicate a brand that is pending deletion.')
                ->warning()
                ->send();

            return;
        }

        $duplicate = BrandManager::duplicate($source);

        $this->refreshBrandListComputed();

        Notification::make()
            ->title('Brand duplicated')
            ->body("Copy created as {$duplicate->code} (inactive). Review and activate when ready.")
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.catalog.brands-page'),
        ]);
    }
}
