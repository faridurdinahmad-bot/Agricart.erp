@php
    $brandCategorySearchOpen = $this->brandCategorySearchOpen;
    $selectedCategories = $this->selectedBrandCategories();
@endphp

<div class="agricart-category-form__field agricart-brand-form__assigned-categories">
    <span class="agricart-category-form__label">Assigned Categories</span>
    <p class="agricart-category-form__hint">Search and select one or more categories for classification and filtering.</p>

    @if ($selectedCategories !== [])
        <div class="agricart-brand-form__category-chips" role="list" aria-label="Selected categories">
            @foreach ($selectedCategories as $category)
                <span class="agricart-brand-form__category-chip" role="listitem" wire:key="brand-category-chip-{{ $category['id'] }}">
                    <span>{{ str_repeat('— ', $category['depth']).$category['label'] }}</span>
                    <button
                        type="button"
                        class="agricart-brand-form__category-chip-remove"
                        wire:click="removeBrandCategory({{ $category['id'] }})"
                        aria-label="Remove {{ $category['label'] }}"
                    >&times;</button>
                </span>
            @endforeach
        </div>
    @endif

    <div class="agricart-category-form__parent-search">
        <div
            class="agricart-category-form__parent-trigger"
            role="combobox"
            aria-expanded="{{ $brandCategorySearchOpen ? 'true' : 'false' }}"
        >
            <button
                type="button"
                class="agricart-category-form__parent-trigger-main"
                wire:click="toggleBrandCategorySearch"
            >
                <span class="agricart-category-form__parent-trigger-label">
                    {{ $selectedCategories === [] ? 'Search categories…' : count($selectedCategories).' selected' }}
                </span>
            </button>
            <span class="agricart-category-form__parent-trigger-actions">
                <span @class([
                    'agricart-category-form__parent-chevron',
                    'is-open' => $brandCategorySearchOpen,
                ]) aria-hidden="true"></span>
            </span>
        </div>

        @if ($brandCategorySearchOpen)
            <div class="agricart-category-form__parent-dropdown">
                <input
                    type="search"
                    class="agricart-category-form__parent-search-input"
                    placeholder="Search categories at any level..."
                    wire:model.live.debounce.150ms="brandCategorySearchQuery"
                    wire:keydown.escape="closeBrandCategorySearch"
                >
                <div class="agricart-category-form__parent-options">
                    @foreach ($this->filteredBrandCategoryOptions() as $option)
                        <button
                            type="button"
                            wire:key="brand-category-option-{{ $option['id'] }}"
                            class="agricart-category-form__parent-option @if($this->isBrandCategorySelected((int) $option['id'])) is-selected @endif"
                            wire:click="toggleBrandCategory('{{ $option['id'] }}')"
                        >
                            <span>{{ str_repeat('— ', $option['depth'] ?? 0).$option['label'] }}</span>
                        </button>
                    @endforeach

                    @if (! $this->hasAnyBrandCategoryOptions())
                        <p class="agricart-category-form__parent-empty">
                            No categories available yet. Create categories first, then assign them to brands.
                        </p>
                    @elseif (! $this->hasBrandCategoryOptions())
                        <p class="agricart-category-form__parent-empty">
                            No categories match your search.
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
