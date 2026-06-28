@php
    $breadcrumbPath = $this->categoryBreadcrumbPath;
    $categoryImage = $this->categoryImage;
    $existingCategoryImageUrl = $this->existingCategoryImageUrl;
    $isEditing = filled($this->editingCategoryId);
    $imageCardLabel = $isEditing
        ? (filled($this->categoryForm['english_name'] ?? null) ? $this->categoryForm['english_name'] : 'Category image')
        : 'New category';
@endphp

<div class="agricart-category-form__hierarchy-strip">
    <div class="agricart-category-form__hierarchy-track">
        @foreach ($breadcrumbPath as $crumb)
            <div class="agricart-category-form__hierarchy-card" wire:key="category-hierarchy-{{ md5($crumb['name']) }}">
                <div class="agricart-category-form__hierarchy-card-media">
                    @if (filled($crumb['image'] ?? null))
                        <img
                            src="{{ $crumb['image'] }}"
                            alt="{{ $crumb['name'] }}"
                            class="agricart-category-form__hierarchy-card-image"
                        >
                    @else
                        <span
                            class="agricart-category-form__hierarchy-card-fallback"
                            style="background-color: {{ $crumb['color'] ?? '#83B735' }}"
                        >{{ mb_substr($crumb['name'], 0, 1) }}</span>
                    @endif
                </div>
                <span class="agricart-category-form__hierarchy-card-label">{{ $crumb['name'] }}</span>
            </div>
        @endforeach

        <div class="agricart-category-form__hierarchy-card agricart-category-form__hierarchy-card--new">
            @include('filament.catalog.partials.category-form-upload', [
                'live' => true,
                'categoryImage' => $categoryImage ?? null,
                'existingCategoryImageUrl' => $existingCategoryImageUrl ?? null,
            ])
            <span class="agricart-category-form__hierarchy-card-label agricart-category-form__hierarchy-card-label--new">{{ $imageCardLabel }}</span>
        </div>
    </div>
</div>
