{{-- Category Form UI — reference: Add Category modal (hierarchy strip + searchable parent) --}}
@php
    $live = $live ?? false;
    $categoryCodeDisplay = $categoryCodeDisplay ?? '';
    $lastAiGeneratedDisplay = $lastAiGeneratedDisplay ?? 'Not generated yet';

    $categoryPreviewThumb = static function (string $letter, string $color): string {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80">'
            .'<rect width="80" height="80" rx="8" fill="'.$color.'"/>'
            .'<text x="40" y="48" text-anchor="middle" fill="#ffffff" font-size="28" font-family="Arial" font-weight="700">'
            .htmlspecialchars($letter, ENT_QUOTES)
            .'</text></svg>';

        return 'data:image/svg+xml,'.rawurlencode($svg);
    };

    if (! $live) {
        $parentOptions = $parentOptions ?? [
            ['id' => '', 'label' => 'Root level (no parent)', 'depth' => 0, 'path' => []],
            ['id' => 'caster-root', 'label' => 'Caster And Trolley Wheels', 'depth' => 0, 'path' => [
                ['name' => 'Caster And Trolley Wheels', 'color' => '#64748b', 'image' => $categoryPreviewThumb('C', '#64748b')],
            ]],
            ['id' => 'furniture-castor', 'label' => 'Furniture Castor Wheels', 'depth' => 1, 'path' => [
                ['name' => 'Caster And Trolley Wheels', 'color' => '#64748b', 'image' => $categoryPreviewThumb('C', '#64748b')],
                ['name' => 'Furniture Castor Wheels', 'color' => '#3B82F6', 'image' => $categoryPreviewThumb('F', '#3B82F6')],
            ]],
            ['id' => 'china-polyurethane', 'label' => 'China Polyurethane Caster Wheels', 'depth' => 2, 'path' => [
                ['name' => 'Caster And Trolley Wheels', 'color' => '#64748b', 'image' => $categoryPreviewThumb('C', '#64748b')],
                ['name' => 'Furniture Castor Wheels', 'color' => '#3B82F6', 'image' => $categoryPreviewThumb('F', '#3B82F6')],
                ['name' => 'China Polyurethane Caster Wheels', 'color' => '#83B735', 'image' => $categoryPreviewThumb('P', '#83B735')],
            ]],
            ['id' => 'agriculture', 'label' => 'Agriculture', 'depth' => 0, 'path' => [
                ['name' => 'Agriculture', 'color' => '#83B735', 'image' => $categoryPreviewThumb('A', '#83B735')],
            ]],
            ['id' => 'irrigation', 'label' => 'Irrigation', 'depth' => 1, 'path' => [
                ['name' => 'Agriculture', 'color' => '#83B735', 'image' => $categoryPreviewThumb('A', '#83B735')],
                ['name' => 'Irrigation', 'color' => '#3B82F6', 'image' => $categoryPreviewThumb('I', '#3B82F6')],
            ]],
        ];
    }
@endphp

@php
    $categoryAdditionalOpen = $categoryAdditionalOpen ?? false;
@endphp

@if ($live)
<div class="agricart-category-form">
    <div class="agricart-category-form__field agricart-category-form__field--parent">
        <span class="agricart-category-form__label">Parent Category</span>
        @include('filament.catalog.partials.category-form-parent-search-live')
    </div>

    @include('filament.catalog.partials.category-form-hierarchy-strip-live')

    <div class="agricart-category-form__names-row">
        <div class="agricart-category-form__field agricart-category-form__field--english">
            <label class="agricart-category-form__label agricart-category-form__label--required" for="category_english_name">English Name</label>
            <div class="agricart-category-form__control-wrap">
                <input
                    id="category_english_name"
                    type="text"
                    class="agricart-category-form__control agricart-category-form__control--with-action"
                    placeholder="Auto-filled from image filename"
                    wire:model="categoryForm.english_name"
                >
                <button
                    type="button"
                    class="agricart-category-form__control-action"
                    wire:click="generateCategoryAiContent"
                    wire:loading.attr="disabled"
                    wire:target="generateCategoryAiContent"
                    title="Generate content with AI"
                    aria-label="Generate content with AI"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" wire:loading.remove wire:target="generateCategoryAiContent">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                    <span wire:loading wire:target="generateCategoryAiContent" class="agricart-category-form__control-action-loading">…</span>
                </button>
            </div>
            @error('categoryForm.english_name') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-category-form__field agricart-category-form__field--urdu">
            <label class="agricart-category-form__label agricart-category-form__label--required" for="category_urdu_name">Urdu Name</label>
            <input
                id="category_urdu_name"
                type="text"
                class="agricart-category-form__control"
                placeholder="اردو نام"
                dir="rtl"
                lang="ur"
                wire:model="categoryForm.urdu_name"
            >
            @error('categoryForm.urdu_name') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="agricart-category-form__collapsible">
        <button
            type="button"
            class="agricart-category-form__collapsible-header"
            wire:click="toggleCategoryAdditional"
            aria-expanded="{{ $this->categoryAdditionalOpen ? 'true' : 'false' }}"
        >
            <span class="agricart-category-form__collapsible-title">Additional Information</span>
            <span @class([
                'agricart-category-form__collapsible-chevron',
                'is-open' => $this->categoryAdditionalOpen,
            ]) aria-hidden="true"></span>
        </button>
        <div @class([
            'agricart-category-form__collapsible-body',
            'is-open' => $this->categoryAdditionalOpen,
        ])>
            @include('filament.catalog.partials.category-form-additional-body', [
                'live' => true,
            ])
        </div>
    </div>
</div>
@else
<div class="agricart-category-form" x-data="agricartCategoryForm(@js($parentOptions))">
    <div class="agricart-category-form__field agricart-category-form__field--parent">
        <span class="agricart-category-form__label">Parent Category</span>
        @include('filament.catalog.partials.category-form-parent-search-preview')
    </div>

    @include('filament.catalog.partials.category-form-hierarchy-strip-preview')

    <div class="agricart-category-form__names-row">
        <div class="agricart-category-form__field agricart-category-form__field--english">
            <label class="agricart-category-form__label agricart-category-form__label--required" for="category_english_name">English Name</label>
            <div class="agricart-category-form__control-wrap">
                <input
                    id="category_english_name"
                    type="text"
                    class="agricart-category-form__control agricart-category-form__control--with-action"
                    placeholder="Auto-filled from image filename"
                    x-model="englishName"
                >
                <button
                    type="button"
                    class="agricart-category-form__control-action"
                    x-on:click.stop="generateWithAi()"
                    title="Generate content with AI"
                    aria-label="Generate content with AI"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="agricart-category-form__field agricart-category-form__field--urdu">
            <label class="agricart-category-form__label agricart-category-form__label--required" for="category_urdu_name">Urdu Name</label>
            <input
                id="category_urdu_name"
                type="text"
                class="agricart-category-form__control"
                placeholder="اردو نام"
                dir="rtl"
                lang="ur"
                x-model="urduName"
            >
        </div>
    </div>

    <div class="agricart-category-form__collapsible">
        <button
            type="button"
            class="agricart-category-form__collapsible-header"
            x-on:click.stop="additionalOpen = ! additionalOpen"
            x-bind:aria-expanded="additionalOpen"
        >
            <span class="agricart-category-form__collapsible-title">Additional Information</span>
            <span class="agricart-category-form__collapsible-chevron" x-bind:class="{ 'is-open': additionalOpen }" aria-hidden="true"></span>
        </button>
        <div class="agricart-category-form__collapsible-body" x-bind:class="{ 'is-open': additionalOpen }">
            @include('filament.catalog.partials.category-form-additional-body', [
                'live' => false,
                'lastAiGeneratedDisplay' => $lastAiGeneratedDisplay,
                'categoryCodeDisplay' => $categoryCodeDisplay,
            ])
        </div>
    </div>
</div>
@endif
