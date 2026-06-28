<div class="agricart-category-form__parent-search" x-on:click.outside="parentSearchOpen = false">
    <div
        class="agricart-category-form__parent-trigger"
        role="combobox"
        x-bind:aria-expanded="parentSearchOpen"
    >
        <button
            type="button"
            class="agricart-category-form__parent-trigger-main"
            x-on:click.stop="parentSearchOpen = ! parentSearchOpen"
        >
            <span
                class="agricart-category-form__parent-trigger-label"
                x-text="selectedParentLabel"
            >Root level (no parent)</span>
        </button>
        <span class="agricart-category-form__parent-trigger-actions">
            <button
                type="button"
                class="agricart-category-form__parent-clear"
                x-show="parentId"
                x-cloak
                x-on:click.stop="clearParent()"
                aria-label="Clear parent category"
            >&times;</button>
            <span class="agricart-category-form__parent-chevron" x-bind:class="{ 'is-open': parentSearchOpen }" aria-hidden="true"></span>
        </span>
    </div>

    <div class="agricart-category-form__parent-dropdown" x-show="parentSearchOpen" x-cloak>
        <input
            type="search"
            class="agricart-category-form__parent-search-input"
            placeholder="Search categories at any level..."
            x-model="parentSearchQuery"
            x-on:keydown.escape.prevent="parentSearchOpen = false"
        >
        <div class="agricart-category-form__parent-options">
            <button
                type="button"
                class="agricart-category-form__parent-option"
                x-bind:class="{ 'is-selected': ! parentId }"
                x-on:click="selectParent('')"
            >
                Root level (no parent)
            </button>
            <template x-for="option in filteredParentOptions" :key="option.id">
                <button
                    type="button"
                    class="agricart-category-form__parent-option"
                    x-bind:class="{ 'is-selected': String(parentId) === String(option.id) }"
                    x-on:click="selectParent(option.id)"
                >
                    <span x-text="`${'\u2014 '.repeat(option.depth ?? 0)}${option.label}`"></span>
                </button>
            </template>
            <p class="agricart-category-form__parent-empty" x-show="! hasSelectableParentOptions" x-cloak>
                No categories available yet. Your first category will be created at root level.
            </p>
            <p class="agricart-category-form__parent-empty" x-show="hasSelectableParentOptions && filteredParentOptions.length === 0" x-cloak>
                No categories match your search.
            </p>
        </div>
    </div>
</div>
