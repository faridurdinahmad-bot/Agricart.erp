document.addEventListener('alpine:init', () => {
    registerAgricartCategoryForm();
});

const registerAgricartCategoryForm = () => {
    Alpine.data('agricartCategoryForm', (parentOptions = []) => ({
        parentOptions,
        parentId: '',
        parentSearchOpen: false,
        parentSearchQuery: '',
        additionalOpen: false,
        aiPromptOpen: false,
        englishName: '',
        urduName: '',
        imagePreview: null,
        hsCode: '',
        displayOrder: '',
        isActive: '1',
        shortDescriptionEn: '',
        shortDescriptionUr: '',
        longDescriptionEn: '',
        longDescriptionUr: '',
        usageEn: '',
        usageUr: '',
        benefitsEn: '',
        benefitsUr: '',
        warningsEn: '',
        warningsUr: '',
        seoTitle: '',
        seoFocusKeywordEn: '',
        seoFocusKeywordUr: '',
        metaDescription: '',
        metaKeywords: '',
        urlSlug: '',
        lastAiGenerated: '',
        canonicalUrl: '',
        metaRobots: 'index, follow',
        ogTitle: '',
        ogDescription: '',
        synonymsEn: '',
        synonymsUr: '',
        alternateSpellings: '',
        searchAliases: '',
        aiPromptOverride: '',
        internalTags: '',

        get selectableParentOptions() {
            return this.parentOptions.filter((option) => option.id !== '');
        },

        get filteredParentOptions() {
            const query = this.parentSearchQuery.trim().toLowerCase();

            if (! query) {
                return this.selectableParentOptions;
            }

            return this.selectableParentOptions.filter((option) => {
                if (option.label.toLowerCase().includes(query)) {
                    return true;
                }

                return (option.path ?? []).some((crumb) => crumb.name.toLowerCase().includes(query));
            });
        },

        get hasSelectableParentOptions() {
            return this.selectableParentOptions.length > 0;
        },

        get selectedParentOption() {
            if (! this.parentId) {
                return null;
            }

            return this.parentOptions.find((option) => String(option.id) === String(this.parentId)) ?? null;
        },

        get selectedParentLabel() {
            return this.selectedParentOption?.label ?? 'Root level (no parent)';
        },

        get hierarchyPath() {
            return this.selectedParentOption?.path ?? [];
        },

        selectParent(id) {
            this.parentId = id;
            this.parentSearchOpen = false;
            this.parentSearchQuery = '';
        },

        clearParent() {
            this.selectParent('');
        },

        generateWithAi() {
            // Placeholder — wired to Livewire / API in a later phase.
        },

        onImageSelect(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            if (this.imagePreview) {
                URL.revokeObjectURL(this.imagePreview);
            }

            this.imagePreview = URL.createObjectURL(file);

            const baseName = file.name.replace(/\.[^/.]+$/, '');
            this.englishName = baseName
                .replace(/[-_]+/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());

            if (! this.urlSlug) {
                this.urlSlug = baseName
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        },
    }));
};

if (window.Alpine) {
    registerAgricartCategoryForm();
}
