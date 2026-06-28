document.addEventListener('alpine:init', () => {
    registerAgricartBrandForm();
});

const registerAgricartBrandForm = () => {
    Alpine.data('agricartBrandForm', (categoryOptions = []) => ({
        categoryOptions,
        categorySearchOpen: false,
        categorySearchQuery: '',
        selectedCategoryIds: [],
        additionalOpen: false,
        englishName: '',
        urduName: '',
        shortNote: '',
        logoPreview: null,
        isActive: '1',
        shortDescriptionEn: '',
        shortDescriptionUr: '',
        longDescriptionEn: '',
        longDescriptionUr: '',
        brandOverviewEn: '',
        brandOverviewUr: '',
        seoTitle: '',
        seoDescription: '',
        seoKeywords: '',
        country: '',
        websiteProtocol: 'https://www.',
        websiteDomain: '',

        get filteredCategoryOptions() {
            const query = this.categorySearchQuery.trim().toLowerCase();

            if (! query) {
                return this.categoryOptions.map((option) => ({
                    id: option.id,
                    label: `${'— '.repeat(option.depth ?? 0)}${option.label}`,
                }));
            }

            return this.categoryOptions
                .filter((option) => {
                    if (option.label.toLowerCase().includes(query)) {
                        return true;
                    }

                    return (option.path ?? []).some((crumb) => crumb.name.toLowerCase().includes(query));
                })
                .map((option) => ({
                    id: option.id,
                    label: `${'— '.repeat(option.depth ?? 0)}${option.label}`,
                }));
        },

        get selectedCategories() {
            return this.selectedCategoryIds
                .map((id) => {
                    const option = this.categoryOptions.find((row) => String(row.id) === String(id));

                    if (! option) {
                        return null;
                    }

                    return {
                        id,
                        label: `${'— '.repeat(option.depth ?? 0)}${option.label}`,
                    };
                })
                .filter(Boolean);
        },

        isCategorySelected(id) {
            return this.selectedCategoryIds.includes(String(id));
        },

        toggleCategory(id) {
            const key = String(id);

            if (this.selectedCategoryIds.includes(key)) {
                this.selectedCategoryIds = this.selectedCategoryIds.filter((value) => value !== key);
            } else {
                this.selectedCategoryIds = [...this.selectedCategoryIds, key];
            }
        },

        removeCategory(id) {
            const key = String(id);
            this.selectedCategoryIds = this.selectedCategoryIds.filter((value) => value !== key);
        },

        generateWithAi() {
            // Live form uses Livewire generateBrandAiContent().
        },

        onLogoSelect(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            const allowedExtensions = ['webp'];
            const allowedTypes = ['image/webp'];
            const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

            if (! allowedExtensions.includes(extension) || (file.type && ! allowedTypes.includes(file.type))) {
                event.target.value = '';
                window.alert('Only WebP images are allowed for brand logos.');
                return;
            }

            if (this.logoPreview) {
                URL.revokeObjectURL(this.logoPreview);
            }

            this.logoPreview = URL.createObjectURL(file);

            const baseName = file.name.replace(/\.[^/.]+$/, '');
            this.englishName = baseName
                .replace(/[-_]+/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());
        },
    }));
};

if (window.Alpine) {
    registerAgricartBrandForm();
}
