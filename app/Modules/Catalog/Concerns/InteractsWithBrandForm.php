<?php

namespace App\Modules\Catalog\Concerns;

use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Catalog\Brand;
use App\Modules\Catalog\Services\BrandAiContentService;
use App\Modules\Catalog\Services\BrandImageStorage;
use App\Modules\Catalog\Services\BrandManager;
use App\Modules\Catalog\Support\BrandLogoSpec;
use App\Modules\Catalog\Support\BrandWebsiteFormatter;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

trait InteractsWithBrandForm
{
    use WithFileUploads;

    public ?int $editingBrandId = null;

    /** @var array<string, mixed> */
    public array $brandForm = [];

    public $brandLogo = null;

    public ?string $existingBrandLogoUrl = null;

    public string $brandCodeDisplay = '';

    public string $lastAiGeneratedDisplay = 'Not generated yet';

    public bool $brandAdditionalOpen = false;

    public bool $brandCategorySearchOpen = false;

    public string $brandCategorySearchQuery = '';

    public function resetBrandForm(): void
    {
        $this->editingBrandId = null;
        $this->brandForm = self::emptyBrandForm();
        $this->brandLogo = null;
        $this->existingBrandLogoUrl = null;
        $this->brandCodeDisplay = '';
        $this->lastAiGeneratedDisplay = 'Not generated yet';
        $this->brandAdditionalOpen = false;
        $this->brandCategorySearchOpen = false;
        $this->brandCategorySearchQuery = '';
        $this->resetValidation();
    }

    public function loadBrandForEdit(int $brandId): void
    {
        $brand = Brand::query()->with('categories')->findOrFail($brandId);

        $this->editingBrandId = $brand->id;
        $this->brandForm = self::brandFormFromModel($brand);
        $this->brandLogo = null;
        $this->existingBrandLogoUrl = BrandImageStorage::url($brand->logo_path);
        $this->brandCodeDisplay = $brand->code;
        $this->lastAiGeneratedDisplay = $brand->lastAiGeneratedLabel();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateBrandForm(): array
    {
        $this->validate([
            'brandForm.english_name' => ['required', 'string', 'max:255'],
            'brandForm.urdu_name' => ['required', 'string', 'max:255'],
            'brandForm.short_note' => ['nullable', 'string', 'max:2000'],
            'brandForm.category_ids' => ['nullable', 'array'],
            'brandForm.category_ids.*' => ['integer', 'exists:catalog_categories,id'],
            'brandForm.is_active' => ['required', 'in:0,1'],
            'brandForm.short_description_en' => ['nullable', 'string'],
            'brandForm.short_description_ur' => ['nullable', 'string'],
            'brandForm.long_description_en' => ['nullable', 'string'],
            'brandForm.long_description_ur' => ['nullable', 'string'],
            'brandForm.brand_overview_en' => ['nullable', 'string'],
            'brandForm.brand_overview_ur' => ['nullable', 'string'],
            'brandForm.seo_title' => ['nullable', 'string', 'max:255'],
            'brandForm.seo_description' => ['nullable', 'string'],
            'brandForm.seo_keywords' => ['nullable', 'string', 'max:500'],
            'brandForm.country' => ['nullable', 'string', 'max:100'],
            'brandForm.website_protocol' => ['nullable', 'string', Rule::in(BrandWebsiteFormatter::protocols())],
            'brandForm.website_domain' => ['nullable', 'string', 'max:480'],
            'brandLogo' => $this->brandLogoValidationRules(),
        ], [
            'brandLogo.required' => 'A WebP brand logo is required.',
        ]);

        return self::normalizeValidatedForm($this->brandForm);
    }

    /**
     * @return list<string|int|\Illuminate\Validation\Rules\File>
     */
    protected function brandLogoValidationRules(): array
    {
        $requiresLogo = ! $this->editingBrandId || blank($this->existingBrandLogoUrl);

        return BrandLogoSpec::validationRules(required: $requiresLogo);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function emptyBrandForm(): array
    {
        return array_merge(self::emptyBrandContentFields(), [
            'english_name' => '',
            'urdu_name' => '',
            'short_note' => '',
            'category_ids' => [],
            'is_active' => '1',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function emptyBrandContentFields(): array
    {
        return [
            'short_description_en' => '',
            'short_description_ur' => '',
            'long_description_en' => '',
            'long_description_ur' => '',
            'brand_overview_en' => '',
            'brand_overview_ur' => '',
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'country' => '',
            'website_protocol' => BrandWebsiteFormatter::DEFAULT_PROTOCOL,
            'website_domain' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function brandFormFromModel(Brand $brand): array
    {
        $website = BrandWebsiteFormatter::split($brand->website);

        return [
            'english_name' => $brand->name_en,
            'urdu_name' => $brand->name_ur,
            'short_note' => $brand->short_note ?? '',
            'category_ids' => $brand->categories->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            'is_active' => $brand->is_active ? '1' : '0',
            'short_description_en' => $brand->short_description_en ?? '',
            'short_description_ur' => $brand->short_description_ur ?? '',
            'long_description_en' => $brand->long_description_en ?? '',
            'long_description_ur' => $brand->long_description_ur ?? '',
            'brand_overview_en' => $brand->brand_overview_en ?? '',
            'brand_overview_ur' => $brand->brand_overview_ur ?? '',
            'seo_title' => $brand->seo_title ?? '',
            'seo_description' => $brand->seo_description ?? '',
            'seo_keywords' => $brand->seo_keywords ?? '',
            'country' => $brand->country ?? '',
            'website_protocol' => $website['protocol'],
            'website_domain' => $website['domain'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected static function normalizeValidatedForm(array $validated): array
    {
        return [
            'name_en' => trim($validated['english_name']),
            'name_ur' => trim($validated['urdu_name']),
            'short_note' => self::nullableString($validated['short_note'] ?? null),
            'is_active' => in_array((string) ($validated['is_active'] ?? '1'), ['1', 'true'], true),
            'short_description_en' => self::nullableString($validated['short_description_en'] ?? null),
            'short_description_ur' => self::nullableString($validated['short_description_ur'] ?? null),
            'long_description_en' => self::nullableString($validated['long_description_en'] ?? null),
            'long_description_ur' => self::nullableString($validated['long_description_ur'] ?? null),
            'brand_overview_en' => self::nullableString($validated['brand_overview_en'] ?? null),
            'brand_overview_ur' => self::nullableString($validated['brand_overview_ur'] ?? null),
            'seo_title' => self::nullableString($validated['seo_title'] ?? null),
            'seo_description' => self::nullableString($validated['seo_description'] ?? null),
            'seo_keywords' => self::nullableString($validated['seo_keywords'] ?? null),
            'country' => self::nullableString($validated['country'] ?? null),
            'website' => BrandWebsiteFormatter::merge(
                $validated['website_protocol'] ?? BrandWebsiteFormatter::DEFAULT_PROTOCOL,
                $validated['website_domain'] ?? null,
            ),
            'category_ids' => array_values(array_map(
                fn ($id): int => (int) $id,
                (array) ($validated['category_ids'] ?? []),
            )),
        ];
    }

    protected static function nullableString(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return filled($value) ? $value : null;
    }

    protected function brandLogoUpload(): ?UploadedFile
    {
        if ($this->brandLogo instanceof TemporaryUploadedFile) {
            return $this->brandLogo;
        }

        return null;
    }

    public function updatedBrandLogo(): void
    {
        if (! $this->brandLogo instanceof TemporaryUploadedFile) {
            return;
        }

        if (! BrandLogoSpec::isAllowedUpload($this->brandLogo)) {
            $this->brandLogo = null;
            $this->addError('brandLogo', BrandLogoSpec::invalidTypeMessage());

            return;
        }

        $validator = validator(
            ['brandLogo' => $this->brandLogo],
            ['brandLogo' => BrandLogoSpec::validationRules()],
            [
                'brandLogo.max' => 'Brand logo must not exceed 5 MB.',
            ],
        );

        if ($validator->fails()) {
            $this->brandLogo = null;
            $this->addError('brandLogo', (string) $validator->errors()->first('brandLogo'));

            return;
        }

        $this->resetValidation('brandLogo');

        $baseName = pathinfo($this->brandLogo->getClientOriginalName(), PATHINFO_FILENAME);
        $this->brandForm['english_name'] = Str::title(str_replace(['-', '_'], ' ', $baseName));
    }

    public function updatedBrandFormWebsiteDomain(?string $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $website = BrandWebsiteFormatter::split($value);

        $this->brandForm['website_protocol'] = $website['protocol'];
        $this->brandForm['website_domain'] = $website['domain'];
    }

    public function toggleBrandAdditional(): void
    {
        $this->brandAdditionalOpen = ! $this->brandAdditionalOpen;
    }

    public function toggleBrandCategorySearch(): void
    {
        $this->brandCategorySearchOpen = ! $this->brandCategorySearchOpen;

        if (! $this->brandCategorySearchOpen) {
            $this->brandCategorySearchQuery = '';
        }
    }

    public function closeBrandCategorySearch(): void
    {
        $this->brandCategorySearchOpen = false;
        $this->brandCategorySearchQuery = '';
    }

    public function toggleBrandCategory(string $categoryId): void
    {
        $id = (int) $categoryId;

        if ($id <= 0) {
            return;
        }

        $selected = array_map('intval', (array) ($this->brandForm['category_ids'] ?? []));

        if (in_array($id, $selected, true)) {
            $selected = array_values(array_filter($selected, fn (int $value): bool => $value !== $id));
        } else {
            $selected[] = $id;
        }

        $this->brandForm['category_ids'] = $selected;
    }

    public function removeBrandCategory(int $categoryId): void
    {
        $this->brandForm['category_ids'] = array_values(array_filter(
            array_map('intval', (array) ($this->brandForm['category_ids'] ?? [])),
            fn (int $id): bool => $id !== $categoryId,
        ));
    }

    public function generateBrandAiContent(): void
    {
        $this->authorizePageAction(
            $this->editingBrandId ? PermissionAction::Update : PermissionAction::Create,
        );

        $brand = $this->editingBrandId
            ? Brand::query()->find($this->editingBrandId)
            : null;

        $result = app(BrandAiContentService::class)->generate(
            formState: $this->brandForm,
            brand: $brand,
        );

        if (! $result->success) {
            Notification::make()
                ->title('AI generation failed')
                ->body($result->message)
                ->danger()
                ->send();

            if ($brand) {
                $this->refreshBrandListComputed();
            }

            return;
        }

        foreach ($result->formFields as $field => $value) {
            if ($field === 'website') {
                $website = BrandWebsiteFormatter::split($value);
                $this->brandForm['website_protocol'] = $website['protocol'];
                $this->brandForm['website_domain'] = $website['domain'];

                continue;
            }

            $this->brandForm[$field] = $value;
        }

        $this->lastAiGeneratedDisplay = $result->generatedLabel ?? 'Generated just now';
        $this->brandAdditionalOpen = true;

        if ($brand) {
            $this->refreshBrandListComputed();
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
    public function brandCategorySelectOptions(): array
    {
        return BrandManager::categorySelectOptions();
    }

    /**
     * @return list<array{id: int|string, label: string, depth: int, path: list<array{name: string, color: string, image: string|null}>}>
     */
    public function filteredBrandCategoryOptions(): array
    {
        $options = $this->brandCategorySelectOptions();
        $query = trim($this->brandCategorySearchQuery);

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

    /**
     * @return list<array{id: int, label: string, depth: int}>
     */
    public function selectedBrandCategories(): array
    {
        $selectedIds = array_map('intval', (array) ($this->brandForm['category_ids'] ?? []));
        $chips = [];

        foreach ($this->brandCategorySelectOptions() as $option) {
            $id = (int) $option['id'];

            if (in_array($id, $selectedIds, true)) {
                $chips[] = [
                    'id' => $id,
                    'label' => $option['label'],
                    'depth' => $option['depth'] ?? 0,
                ];
            }
        }

        return $chips;
    }

    public function isBrandCategorySelected(int $categoryId): bool
    {
        return in_array($categoryId, array_map('intval', (array) ($this->brandForm['category_ids'] ?? [])), true);
    }

    public function hasBrandCategoryOptions(): bool
    {
        return count($this->filteredBrandCategoryOptions()) > 0;
    }

    public function hasAnyBrandCategoryOptions(): bool
    {
        return count($this->brandCategorySelectOptions()) > 0;
    }

    protected function refreshBrandListComputed(): void
    {
        unset($this->filteredBrands, $this->paginatedBrands, $this->brandListMeta);
    }
}
