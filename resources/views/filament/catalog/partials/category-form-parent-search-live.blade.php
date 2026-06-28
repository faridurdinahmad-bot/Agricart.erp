@php
    $categoryParentSearchOpen = $this->categoryParentSearchOpen;
    $categoryForm = $this->categoryForm;
@endphp

<div class="agricart-category-form__parent-search">
    <div
        class="agricart-category-form__parent-trigger"
        role="combobox"
        aria-expanded="{{ $categoryParentSearchOpen ? 'true' : 'false' }}"
    >
        <button
            type="button"
            class="agricart-category-form__parent-trigger-main"
            wire:click="toggleCategoryParentSearch"
        >
            <span class="agricart-category-form__parent-trigger-label">
                {{ $this->selectedCategoryParentLabel() }}
            </span>
        </button>
        <span class="agricart-category-form__parent-trigger-actions">
            @if (filled($categoryForm['parent_id'] ?? null))
                <button
                    type="button"
                    class="agricart-category-form__parent-clear"
                    wire:click.stop="clearCategoryParent"
                    aria-label="Clear parent category"
                >&times;</button>
            @endif
            <span @class([
                'agricart-category-form__parent-chevron',
                'is-open' => $categoryParentSearchOpen,
            ]) aria-hidden="true"></span>
        </span>
    </div>

    @if ($categoryParentSearchOpen)
        <div class="agricart-category-form__parent-dropdown">
            <input
                type="search"
                class="agricart-category-form__parent-search-input"
                placeholder="Search categories at any level..."
                wire:model.live.debounce.150ms="categoryParentSearchQuery"
                wire:keydown.escape="closeCategoryParentSearch"
            >
            <div class="agricart-category-form__parent-options">
                <button
                    type="button"
                    class="agricart-category-form__parent-option @if(! filled($categoryForm['parent_id'] ?? null)) is-selected @endif"
                    wire:click="selectCategoryParent('')"
                >
                    Root level (no parent)
                </button>

                @foreach ($this->filteredCategoryParentOptions() as $option)
                    <button
                        type="button"
                        wire:key="category-parent-option-{{ $option['id'] }}"
                        class="agricart-category-form__parent-option @if((string) ($categoryForm['parent_id'] ?? '') === (string) $option['id']) is-selected @endif"
                        wire:click="selectCategoryParent('{{ $option['id'] }}')"
                    >
                        <span>{{ str_repeat('— ', $option['depth'] ?? 0).$option['label'] }}</span>
                    </button>
                @endforeach

                @if (! $this->hasAnyCategoryParentOptions())
                    <p class="agricart-category-form__parent-empty">
                        No categories available yet. Your first category will be created at root level.
                    </p>
                @elseif (! $this->hasCategoryParentOptions())
                    <p class="agricart-category-form__parent-empty">
                        No categories match your search.
                    </p>
                @endif
            </div>
        </div>
    @endif
</div>
