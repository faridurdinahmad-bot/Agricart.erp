<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Catalog\Category;
use App\Modules\Catalog\Services\CategoryAiContentService;
use App\Modules\Catalog\Services\CategoryImageStorage;
use App\Modules\Catalog\Services\CategoryManager;
use App\Modules\Catalog\Support\CatalogWebpImageSpec;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

trait InteractsWithCategoryForm
{
    use WithFileUploads;

    public ?int $editingCategoryId = null;

    /** @var array<string, mixed> */
    public array $categoryForm = [];

    public $categoryImage = null;

    public ?string $existingCategoryImageUrl = null;

    public string $categoryCodeDisplay = '';

    public string $categoryCanonicalDisplay = 'Generated automatically on save';

    public string $categorySlugDisplay = 'Generated automatically on save';

    public string $lastAiGeneratedDisplay = 'Not generated yet';

    public bool $categoryParentSearchOpen = false;

    public bool $categoryAdditionalOpen = false;

    public bool $categoryAiPromptOpen = false;

    public string $categoryParentSearchQuery = '';

    public function resetCategoryForm(): void
    {
        $this->editingCategoryId = null;
        $this->categoryForm = self::emptyCategoryForm();
        $this->categoryImage = null;
        $this->existingCategoryImageUrl = null;
        $this->categoryCodeDisplay = '';
        $this->categorySlugDisplay = 'Generated automatically on save';
        $this->categoryCanonicalDisplay = 'Generated automatically on save';
        $this->lastAiGeneratedDisplay = 'Not generated yet';
        $this->categoryParentSearchOpen = false;
        $this->categoryAdditionalOpen = false;
        $this->categoryAiPromptOpen = false;
        $this->categoryParentSearchQuery = '';
        $this->resetValidation();
    }

    public function loadCategoryForEdit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->editingCategoryId = $category->id;
        $this->categoryForm = self::categoryFormFromModel($category);
        $this->categoryImage = null;
        $this->existingCategoryImageUrl = CategoryImageStorage::url($category->image_path);
        $this->categoryCodeDisplay = $category->code;
        $this->categorySlugDisplay = filled($category->url_slug) ? $category->url_slug : 'Generated automatically on save';
        $this->categoryCanonicalDisplay = filled($category->canonical_url) ? $category->canonical_url : 'Generated automatically on save';
        $this->lastAiGeneratedDisplay = $category->lastAiGeneratedLabel();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateCategoryForm(): array
    {
        $this->validate([
            'categoryForm.parent_id' => ['nullable', 'string'],
            'categoryForm.english_name' => ['required', 'string', 'max:255'],
            'categoryForm.urdu_name' => ['required', 'string', 'max:255'],
            'categoryForm.hs_code' => ['nullable', 'string', 'max:50'],
            'categoryForm.display_order' => ['nullable', 'integer', 'min:0'],
            'categoryForm.is_active' => ['required', 'in:0,1'],
            'categoryForm.short_description_en' => ['nullable', 'string'],
            'categoryForm.short_description_ur' => ['nullable', 'string'],
            'categoryForm.long_description_en' => ['nullable', 'string'],
            'categoryForm.long_description_ur' => ['nullable', 'string'],
            'categoryForm.usage_en' => ['nullable', 'string'],
            'categoryForm.usage_ur' => ['nullable', 'string'],
            'categoryForm.benefits_en' => ['nullable', 'string'],
            'categoryForm.benefits_ur' => ['nullable', 'string'],
            'categoryForm.warnings_en' => ['nullable', 'string'],
            'categoryForm.warnings_ur' => ['nullable', 'string'],
            'categoryForm.seo_title' => ['nullable', 'string', 'max:255'],
            'categoryForm.seo_focus_keyword_en' => ['nullable', 'string', 'max:255'],
            'categoryForm.seo_focus_keyword_ur' => ['nullable', 'string', 'max:255'],
            'categoryForm.meta_description' => ['nullable', 'string'],
            'categoryForm.meta_keywords' => ['nullable', 'string', 'max:500'],
            'categoryForm.meta_robots' => ['nullable', 'string', 'max:100'],
            'categoryForm.og_title' => ['nullable', 'string', 'max:255'],
            'categoryForm.og_description' => ['nullable', 'string'],
            'categoryForm.synonyms_en' => ['nullable', 'string', 'max:500'],
            'categoryForm.synonyms_ur' => ['nullable', 'string', 'max:500'],
            'categoryForm.alternate_spellings' => ['nullable', 'string', 'max:500'],
            'categoryForm.search_aliases' => ['nullable', 'string', 'max:500'],
            'categoryForm.ai_prompt_override' => ['nullable', 'string'],
            'categoryForm.internal_tags' => ['nullable', 'string', 'max:500'],
            'categoryImage' => CatalogWebpImageSpec::validationRules(),
        ]);

        return self::normalizeValidatedForm($this->categoryForm);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function emptyCategoryForm(): array
    {
        return [
            'parent_id' => '',
            'english_name' => '',
            'urdu_name' => '',
            'hs_code' => '',
            'display_order' => 0,
            'is_active' => '1',
            'short_description_en' => '',
            'short_description_ur' => '',
            'long_description_en' => '',
            'long_description_ur' => '',
            'usage_en' => '',
            'usage_ur' => '',
            'benefits_en' => '',
            'benefits_ur' => '',
            'warnings_en' => '',
            'warnings_ur' => '',
            'seo_title' => '',
            'seo_focus_keyword_en' => '',
            'seo_focus_keyword_ur' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'meta_robots' => 'index, follow',
            'og_title' => '',
            'og_description' => '',
            'synonyms_en' => '',
            'synonyms_ur' => '',
            'alternate_spellings' => '',
            'search_aliases' => '',
            'ai_prompt_override' => '',
            'internal_tags' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function categoryFormFromModel(Category $category): array
    {
        return [
            'parent_id' => $category->parent_id ? (string) $category->parent_id : '',
            'english_name' => $category->name_en,
            'urdu_name' => $category->name_ur,
            'hs_code' => $category->hs_code ?? '',
            'display_order' => $category->display_order,
            'is_active' => $category->is_active ? '1' : '0',
            'short_description_en' => $category->short_description_en ?? '',
            'short_description_ur' => $category->short_description_ur ?? '',
            'long_description_en' => $category->long_description_en ?? '',
            'long_description_ur' => $category->long_description_ur ?? '',
            'usage_en' => $category->usage_en ?? '',
            'usage_ur' => $category->usage_ur ?? '',
            'benefits_en' => $category->benefits_en ?? '',
            'benefits_ur' => $category->benefits_ur ?? '',
            'warnings_en' => $category->warnings_en ?? '',
            'warnings_ur' => $category->warnings_ur ?? '',
            'seo_title' => $category->seo_title ?? '',
            'seo_focus_keyword_en' => $category->seo_focus_keyword_en ?? '',
            'seo_focus_keyword_ur' => $category->seo_focus_keyword_ur ?? '',
            'meta_description' => $category->meta_description ?? '',
            'meta_keywords' => $category->meta_keywords ?? '',
            'meta_robots' => $category->meta_robots ?? 'index, follow',
            'og_title' => $category->og_title ?? '',
            'og_description' => $category->og_description ?? '',
            'synonyms_en' => $category->synonyms_en ?? '',
            'synonyms_ur' => $category->synonyms_ur ?? '',
            'alternate_spellings' => $category->alternate_spellings ?? '',
            'search_aliases' => $category->search_aliases ?? '',
            'ai_prompt_override' => $category->ai_prompt_override ?? '',
            'internal_tags' => $category->internal_tags ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected static function normalizeValidatedForm(array $validated): array
    {
        $parentId = filled($validated['parent_id'] ?? null)
            ? (int) $validated['parent_id']
            : null;

        return [
            'parent_id' => $parentId,
            'name_en' => trim($validated['english_name']),
            'name_ur' => trim($validated['urdu_name']),
            'hs_code' => filled($validated['hs_code'] ?? null) ? trim($validated['hs_code']) : null,
            'display_order' => (int) ($validated['display_order'] ?? 0),
            'is_active' => in_array((string) ($validated['is_active'] ?? '1'), ['1', 'true'], true),
            'short_description_en' => self::nullableString($validated['short_description_en'] ?? null),
            'short_description_ur' => self::nullableString($validated['short_description_ur'] ?? null),
            'long_description_en' => self::nullableString($validated['long_description_en'] ?? null),
            'long_description_ur' => self::nullableString($validated['long_description_ur'] ?? null),
            'usage_en' => self::nullableString($validated['usage_en'] ?? null),
            'usage_ur' => self::nullableString($validated['usage_ur'] ?? null),
            'benefits_en' => self::nullableString($validated['benefits_en'] ?? null),
            'benefits_ur' => self::nullableString($validated['benefits_ur'] ?? null),
            'warnings_en' => self::nullableString($validated['warnings_en'] ?? null),
            'warnings_ur' => self::nullableString($validated['warnings_ur'] ?? null),
            'seo_title' => self::nullableString($validated['seo_title'] ?? null),
            'seo_focus_keyword_en' => self::nullableString($validated['seo_focus_keyword_en'] ?? null),
            'seo_focus_keyword_ur' => self::nullableString($validated['seo_focus_keyword_ur'] ?? null),
            'meta_description' => self::nullableString($validated['meta_description'] ?? null),
            'meta_keywords' => self::nullableString($validated['meta_keywords'] ?? null),
            'meta_robots' => $validated['meta_robots'] ?? 'index, follow',
            'og_title' => self::nullableString($validated['og_title'] ?? null),
            'og_description' => self::nullableString($validated['og_description'] ?? null),
            'synonyms_en' => self::nullableString($validated['synonyms_en'] ?? null),
            'synonyms_ur' => self::nullableString($validated['synonyms_ur'] ?? null),
            'alternate_spellings' => self::nullableString($validated['alternate_spellings'] ?? null),
            'search_aliases' => self::nullableString($validated['search_aliases'] ?? null),
            'ai_prompt_override' => self::nullableString($validated['ai_prompt_override'] ?? null),
            'internal_tags' => self::nullableString($validated['internal_tags'] ?? null),
        ];
    }

    protected static function nullableString(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return filled($value) ? $value : null;
    }

    protected function categoryImageUpload(): ?UploadedFile
    {
        if ($this->categoryImage instanceof TemporaryUploadedFile) {
            return $this->categoryImage;
        }

        return null;
    }

    public function updatedCategoryImage(): void
    {
        if (! $this->categoryImage instanceof TemporaryUploadedFile) {
            return;
        }

        if (! CatalogWebpImageSpec::isAllowedUpload($this->categoryImage)) {
            $this->categoryImage = null;
            $this->addError('categoryImage', CatalogWebpImageSpec::invalidTypeMessage());

            return;
        }

        $validator = validator(
            ['categoryImage' => $this->categoryImage],
            ['categoryImage' => CatalogWebpImageSpec::validationRules()],
            [
                'categoryImage.max' => 'Category image must not exceed 5 MB.',
            ],
        );

        if ($validator->fails()) {
            $this->categoryImage = null;
            $this->addError('categoryImage', (string) $validator->errors()->first('categoryImage'));

            return;
        }

        $this->resetValidation('categoryImage');

        $baseName = pathinfo($this->categoryImage->getClientOriginalName(), PATHINFO_FILENAME);
        $this->categoryForm['english_name'] = Str::title(str_replace(['-', '_'], ' ', $baseName));
    }

    public function updatedCategoryFormParentId(): void
    {
        unset($this->categoryBreadcrumbPath);
    }

    public function toggleCategoryParentSearch(): void
    {
        $this->categoryParentSearchOpen = ! $this->categoryParentSearchOpen;

        if (! $this->categoryParentSearchOpen) {
            $this->categoryParentSearchQuery = '';
        }
    }

    public function closeCategoryParentSearch(): void
    {
        $this->categoryParentSearchOpen = false;
        $this->categoryParentSearchQuery = '';
    }

    public function toggleCategoryAdditional(): void
    {
        $this->categoryAdditionalOpen = ! $this->categoryAdditionalOpen;
    }

    public function toggleCategoryAiPrompt(): void
    {
        $this->categoryAiPromptOpen = ! $this->categoryAiPromptOpen;
    }

    public function selectCategoryParent(?string $parentId): void
    {
        $this->categoryForm['parent_id'] = filled($parentId) ? (string) $parentId : '';
        $this->closeCategoryParentSearch();
        unset($this->categoryBreadcrumbPath);
    }

    public function clearCategoryParent(): void
    {
        $this->selectCategoryParent(null);
    }

    public function generateCategoryAiContent(): void
    {
        $this->authorizePageAction(
            $this->editingCategoryId ? PermissionAction::Update : PermissionAction::Create,
        );

        $category = $this->editingCategoryId
            ? Category::query()->find($this->editingCategoryId)
            : null;

        $result = app(CategoryAiContentService::class)->generate(
            formState: $this->categoryForm,
            category: $category,
        );

        if (! $result->success) {
            Notification::make()
                ->title('AI generation failed')
                ->body($result->message)
                ->danger()
                ->send();

            if ($category) {
                unset($this->categoryTree, $this->flatCategories, $this->filteredCategories, $this->paginatedCategories, $this->categoryListMeta);
            }

            return;
        }

        foreach ($result->formFields as $field => $value) {
            $this->categoryForm[$field] = $value;
        }

        $this->lastAiGeneratedDisplay = $result->generatedLabel ?? 'Generated just now';
        $this->categoryAdditionalOpen = true;

        if ($category) {
            unset($this->categoryTree, $this->flatCategories, $this->filteredCategories, $this->paginatedCategories, $this->categoryListMeta);
        }

        Notification::make()
            ->title('AI content generated')
            ->body('Review the generated fields in Additional Information, then save.')
            ->success()
            ->send();
    }

    /**
     * @return list<array{id: int|string, label: string, depth: int, path: list<array{name: string, color: string, image: string|null}>}>
     */
    public function categoryParentSelectOptions(): array
    {
        return CategoryManager::parentSelectOptions($this->editingCategoryId);
    }

    /**
     * @return list<array{id: int|string, label: string, depth: int, path: list<array{name: string, color: string, image: string|null}>}>
     */
    public function filteredCategoryParentOptions(): array
    {
        $options = array_values(array_filter(
            $this->categoryParentSelectOptions(),
            fn (array $option): bool => $option['id'] !== '',
        ));

        $query = trim($this->categoryParentSearchQuery);

        if ($query === '') {
            return $options;
        }

        $needle = strtolower($query);

        return array_values(array_filter($options, function (array $option) use ($needle): bool {
            if (str_contains(strtolower($option['label']), $needle)) {
                return true;
            }

            foreach ($option['path'] ?? [] as $crumb) {
                if (str_contains(strtolower($crumb['name']), $needle)) {
                    return true;
                }
            }

            return false;
        }));
    }

    public function selectedCategoryParentLabel(): string
    {
        $parentId = (string) ($this->categoryForm['parent_id'] ?? '');

        if ($parentId === '') {
            return 'Root level (no parent)';
        }

        foreach ($this->categoryParentSelectOptions() as $option) {
            if ((string) $option['id'] === $parentId) {
                return $option['label'];
            }
        }

        return 'Root level (no parent)';
    }

    public function hasCategoryParentOptions(): bool
    {
        return count($this->filteredCategoryParentOptions()) > 0;
    }

    public function hasAnyCategoryParentOptions(): bool
    {
        return count(array_filter(
            $this->categoryParentSelectOptions(),
            fn (array $option): bool => $option['id'] !== '',
        )) > 0;
    }
}
