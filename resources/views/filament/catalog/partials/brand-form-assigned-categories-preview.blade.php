<div class="agricart-category-form__field agricart-brand-form__assigned-categories">
    <span class="agricart-category-form__label">Assigned Categories</span>
    <p class="agricart-category-form__hint">Search and select one or more categories for classification and filtering.</p>

    <div class="agricart-brand-form__category-chips" x-show="selectedCategories.length" role="list" aria-label="Selected categories">
        <template x-for="category in selectedCategories" :key="`brand-category-chip-${category.id}`">
            <span class="agricart-brand-form__category-chip" role="listitem">
                <span x-text="category.label"></span>
                <button
                    type="button"
                    class="agricart-brand-form__category-chip-remove"
                    x-on:click.stop="removeCategory(category.id)"
                    x-bind:aria-label="`Remove ${category.label}`"
                >&times;</button>
            </span>
        </template>
    </div>

    <div class="agricart-category-form__parent-search">
        <div class="agricart-category-form__parent-trigger" role="combobox" x-bind:aria-expanded="categorySearchOpen">
            <button type="button" class="agricart-category-form__parent-trigger-main" x-on:click.stop="categorySearchOpen = ! categorySearchOpen">
                <span class="agricart-category-form__parent-trigger-label" x-text="selectedCategories.length ? `${selectedCategories.length} selected` : 'Search categories…'"></span>
            </button>
            <span class="agricart-category-form__parent-trigger-actions">
                <span class="agricart-category-form__parent-chevron" x-bind:class="{ 'is-open': categorySearchOpen }" aria-hidden="true"></span>
            </span>
        </div>

        <div class="agricart-category-form__parent-dropdown" x-show="categorySearchOpen" x-on:click.outside="categorySearchOpen = false">
            <input
                type="search"
                class="agricart-category-form__parent-search-input"
                placeholder="Search categories at any level..."
                x-model="categorySearchQuery"
            >
            <div class="agricart-category-form__parent-options">
                <template x-for="option in filteredCategoryOptions" :key="`brand-category-option-${option.id}`">
                    <button
                        type="button"
                        class="agricart-category-form__parent-option"
                        x-bind:class="{ 'is-selected': isCategorySelected(option.id) }"
                        x-on:click.stop="toggleCategory(option.id)"
                    >
                        <span x-text="option.label"></span>
                    </button>
                </template>
                <p class="agricart-category-form__parent-empty" x-show="filteredCategoryOptions.length === 0">
                    No categories match your search.
                </p>
            </div>
        </div>
    </div>
</div>
