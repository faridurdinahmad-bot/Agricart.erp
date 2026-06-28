<div class="agricart-category-form__hierarchy-strip">
    <div class="agricart-category-form__hierarchy-track">
        <template x-for="(crumb, index) in hierarchyPath" :key="`hierarchy-${crumb.name}-${index}`">
            <div class="agricart-category-form__hierarchy-card">
                <div class="agricart-category-form__hierarchy-card-media">
                    <img
                        x-show="crumb.image"
                        :src="crumb.image"
                        :alt="crumb.name"
                        class="agricart-category-form__hierarchy-card-image"
                    >
                    <span
                        x-show="! crumb.image"
                        class="agricart-category-form__hierarchy-card-fallback"
                        :style="{ backgroundColor: crumb.color || '#83B735' }"
                        x-text="crumb.name.charAt(0)"
                    ></span>
                </div>
                <span class="agricart-category-form__hierarchy-card-label" x-text="crumb.name"></span>
            </div>
        </template>

        <div class="agricart-category-form__hierarchy-card agricart-category-form__hierarchy-card--new">
            @include('filament.catalog.partials.category-form-upload', [
                'live' => false,
                'categoryImage' => null,
                'existingCategoryImageUrl' => null,
            ])
            <span class="agricart-category-form__hierarchy-card-label agricart-category-form__hierarchy-card-label--new">New category</span>
        </div>
    </div>
</div>
