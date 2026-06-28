<?php

namespace App\Modules\Catalog\Pages;

use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Catalog\Category;
use App\Modules\Catalog\Clusters\CatalogCluster;
use App\Modules\Catalog\Concerns\InteractsWithCategoryContentAuditExport;
use App\Modules\Catalog\Concerns\InteractsWithCategoryContentReview;
use App\Modules\Catalog\Concerns\InteractsWithCategoryDeletionRequests;
use App\Modules\Catalog\Concerns\InteractsWithCategoryForm;
use App\Modules\Catalog\Concerns\InteractsWithCategoryView;
use App\Modules\Catalog\Navigation\CatalogNavigation;
use App\Modules\Catalog\Services\CategoryManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class CategoriesPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal, InteractsWithCategoryContentAuditExport, InteractsWithCategoryContentReview, InteractsWithCategoryDeletionRequests, InteractsWithCategoryForm, InteractsWithCategoryView;

    protected static ?string $cluster = CatalogCluster::class;

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $title = 'Categories';

    protected static ?string $slug = 'categories';

    protected static ?int $navigationSort = CatalogNavigation::CATEGORIES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public string $search = '';

    public string $statusFilter = '';

    public string $aiStatusFilter = '';

    public int $categoriesPage = 1;

    public int $categoriesPerPage = 25;

    public function mount(): void
    {
        $this->resetCategoryForm();
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
        return ['agricart-categories-page'];
    }

    #[Computed]
    public function categoryTree()
    {
        return CategoryManager::tree();
    }

    #[Computed]
    public function flatCategories()
    {
        return $this->flattenCategories($this->categoryTree);
    }

    #[Computed]
    public function categoryParentOptions(): array
    {
        return CategoryManager::parentSelectOptions($this->editingCategoryId);
    }

    #[Computed]
    public function categoryBreadcrumbPath(): array
    {
        $parentId = filled($this->categoryForm['parent_id'] ?? null)
            ? (int) $this->categoryForm['parent_id']
            : null;

        return CategoryManager::breadcrumbPathFor($parentId);
    }

    #[Computed]
    public function filteredCategories(): array
    {
        $rows = $this->flatCategories;

        if ($this->statusFilter === 'active') {
            $rows = array_values(array_filter(
                $rows,
                fn (array $row): bool => $row['category']->is_active,
            ));
        } elseif ($this->statusFilter === 'inactive') {
            $rows = array_values(array_filter(
                $rows,
                fn (array $row): bool => ! $row['category']->is_active,
            ));
        }

        if ($this->aiStatusFilter === 'needs_attention') {
            $rows = array_values(array_filter(
                $rows,
                fn (array $row): bool => $row['category']->needsAiAttention(),
            ));
        } elseif ($this->aiStatusFilter !== '') {
            $rows = array_values(array_filter(
                $rows,
                fn (array $row): bool => ($row['category']->ai_content_status?->value ?? '') === $this->aiStatusFilter,
            ));
        }

        return $rows;
    }

    #[Computed]
    public function paginatedCategories(): array
    {
        $offset = ($this->categoriesPage - 1) * $this->categoriesPerPage;

        return array_slice($this->filteredCategories, $offset, $this->categoriesPerPage);
    }

    /**
     * @return array{total: int, from: int, to: int, currentPage: int, lastPage: int}
     */
    #[Computed]
    public function categoryListMeta(): array
    {
        $total = count($this->filteredCategories);
        $lastPage = max(1, (int) ceil($total / $this->categoriesPerPage));
        $currentPage = min($this->categoriesPage, $lastPage);
        $from = $total === 0 ? 0 : (($currentPage - 1) * $this->categoriesPerPage) + 1;
        $to = min($total, $currentPage * $this->categoriesPerPage);

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
        $this->categoriesPage = 1;
        unset($this->flatCategories, $this->filteredCategories, $this->paginatedCategories, $this->categoryListMeta);
    }

    public function updatedStatusFilter(): void
    {
        $this->categoriesPage = 1;
        unset($this->filteredCategories, $this->paginatedCategories, $this->categoryListMeta);
    }

    public function updatedAiStatusFilter(): void
    {
        $this->categoriesPage = 1;
        unset($this->filteredCategories, $this->paginatedCategories, $this->categoryListMeta);
    }

    public function goToCategoriesPage(int $page): void
    {
        $lastPage = $this->categoryListMeta['lastPage'];
        $this->categoriesPage = max(1, min($page, $lastPage));
        unset($this->paginatedCategories, $this->categoryListMeta);
    }

    public function addCategoryAction(): Action
    {
        return $this->configureCategoryFormAction(
            Action::make('addCategory')
                ->label('Add Category')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->visible(fn (): bool => $this->canPageAction(PermissionAction::Create))
                ->before(function (): void {
                    $this->resetCategoryForm();
                }),
        );
    }

    public function categoryFormAction(): Action
    {
        return $this->configureCategoryFormAction(Action::make('categoryForm'));
    }

    public function openEditCategory(int $categoryId): void
    {
        $this->authorizePageAction(PermissionAction::Update);

        $category = Category::query()->findOrFail($categoryId);

        if ($category->isPendingDeletion()) {
            Notification::make()
                ->title('Category pending deletion')
                ->body('This category cannot be edited while a deletion request is pending approval.')
                ->warning()
                ->send();

            return;
        }

        $this->loadCategoryForEdit($categoryId);
        unset($this->categoryParentOptions, $this->categoryBreadcrumbPath);
        $this->mountAction('categoryForm');
    }

    protected function configureCategoryFormAction(Action $action): Action
    {
        return $action
            ->modalHeading(fn (): string => $this->editingCategoryId ? 'Edit Category' : 'Add Category')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.catalog.category-form', [
                'live' => true,
                'categorySlugDisplay' => $this->categorySlugDisplay,
                'categoryCanonicalDisplay' => $this->categoryCanonicalDisplay,
            ]))
            ->stickyModalFooter()
            ->modalFooterActions([
                Action::make('cancelCategoryForm')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                $this->exportCategoryReviewFooterAction(),
                Action::make('saveAndAddNextCategory')
                    ->label('Save & Add Next')
                    ->outlined()
                    ->visible(fn (): bool => ! $this->editingCategoryId && $this->canPageAction(PermissionAction::Create))
                    ->action(function (): void {
                        $this->saveCategory(addAnother: true);
                    }),
                Action::make('submitCategoryForm')
                    ->label('Save & Close')
                    ->color('primary')
                    ->visible(fn (): bool => $this->editingCategoryId
                        ? $this->canPageAction(PermissionAction::Update)
                        : $this->canPageAction(PermissionAction::Create))
                    ->cancelParentActions()
                    ->action(function (): void {
                        $this->saveCategory();
                    }),
            ]);
    }

    public function saveCategory(bool $addAnother = false): void
    {
        $this->authorizePageAction(
            $this->editingCategoryId ? PermissionAction::Update : PermissionAction::Create,
        );

        try {
            $payload = $this->validateCategoryForm();
            $image = $this->categoryImageUpload();

            if ($this->editingCategoryId) {
                $category = Category::query()->findOrFail($this->editingCategoryId);
                CategoryManager::update($category, $payload, $image, auth()->user());
                $message = 'Category updated successfully.';
            } else {
                CategoryManager::create($payload, $image, auth()->user());
                $message = 'Category created successfully.';
            }

            unset(
                $this->categoryTree,
                $this->flatCategories,
                $this->categoryParentOptions,
                $this->categoryBreadcrumbPath,
                $this->filteredCategories,
                $this->paginatedCategories,
                $this->categoryListMeta,
            );

            $this->resetCategoryForm();

            if ($addAnother) {
                Notification::make()->title($message)->success()->send();
                $this->replaceMountedAction('addCategory');

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

    public function duplicateCategory(int $categoryId): void
    {
        $this->authorizePageAction(PermissionAction::Create);

        $source = Category::query()->findOrFail($categoryId);

        if ($source->isPendingDeletion()) {
            Notification::make()
                ->title('Category pending deletion')
                ->body('Cannot duplicate a category that is pending deletion.')
                ->warning()
                ->send();

            return;
        }

        $duplicate = CategoryManager::duplicate($source);

        unset(
            $this->categoryTree,
            $this->flatCategories,
            $this->categoryParentOptions,
            $this->filteredCategories,
            $this->paginatedCategories,
            $this->categoryListMeta,
        );

        Notification::make()
            ->title('Category duplicated')
            ->body("Copy created as {$duplicate->code} (inactive). Review and activate when ready.")
            ->success()
            ->send();
    }

    public function deleteCategory(int $categoryId): void
    {
        $this->openRequestDeletionModal($categoryId);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.catalog.categories-page'),
            ]);
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return list<array{category: Category, depth: int}>
     */
    protected function flattenCategories($categories, int $depth = 0): array
    {
        $rows = [];

        foreach ($categories as $category) {
            if (filled($this->search)) {
                $term = strtolower($this->search);
                $matches = str_contains(strtolower($category->name_en), $term)
                    || str_contains(strtolower($category->name_ur), $term)
                    || str_contains(strtolower($category->code), $term);

                $childRows = $this->flattenCategories($category->children, $depth + 1);

                if ($matches || $childRows !== []) {
                    if ($matches) {
                        $rows[] = ['category' => $category, 'depth' => $depth];
                    }
                    $rows = [...$rows, ...$childRows];
                }

                continue;
            }

            $rows[] = ['category' => $category, 'depth' => $depth];
            $rows = [...$rows, ...$this->flattenCategories($category->children, $depth + 1)];
        }

        return $rows;
    }
}
