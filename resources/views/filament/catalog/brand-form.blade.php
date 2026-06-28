{{-- Brand Form UI — mirrors Category design language --}}
@php
    $live = $live ?? false;
    $brandCodeDisplay = $brandCodeDisplay ?? '';
    $countryOptions = [
        'Pakistan', 'Japan', 'China', 'Germany', 'United States', 'Sweden', 'Italy', 'United Kingdom', 'India', 'Turkey',
    ];
    $codeDisplay = filled($brandCodeDisplay) ? $brandCodeDisplay : 'Auto-generated on save';
@endphp

@if ($live)
<div class="agricart-category-form agricart-brand-form">
    <div class="agricart-category-form__image-row">
        <div class="agricart-category-form__field agricart-brand-form__logo-field">
            <span class="agricart-category-form__label agricart-category-form__label--required">Brand Logo</span>
            <div class="agricart-brand-form__logo-box">
                @include('filament.catalog.partials.brand-form-upload', [
                    'live' => true,
                    'brandLogo' => $this->brandLogo,
                    'existingBrandLogoUrl' => $this->existingBrandLogoUrl,
                ])
            </div>
            <p class="agricart-category-form__hint">{{ \App\Modules\Catalog\Support\BrandLogoSpec::uploadHint() }}</p>
            @error('brandLogo') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-brand-form__names-stack">
            <div class="agricart-category-form__field agricart-category-form__field--english">
                <label class="agricart-category-form__label agricart-category-form__label--required" for="brand_english_name">English Brand Name</label>
                <div class="agricart-category-form__control-wrap">
                    <input
                        id="brand_english_name"
                        type="text"
                        class="agricart-category-form__control agricart-category-form__control--with-action"
                        placeholder="Auto-filled from logo filename"
                        wire:model="brandForm.english_name"
                    >
                    @if ($this->canPageAction($this->editingBrandId ? \App\Core\Authorization\Enums\PermissionAction::Update : \App\Core\Authorization\Enums\PermissionAction::Create))
                    <button
                        type="button"
                        class="agricart-category-form__control-action"
                        wire:click="generateBrandAiContent"
                        wire:loading.attr="disabled"
                        wire:target="generateBrandAiContent"
                        title="Generate content with AI"
                        aria-label="Generate content with AI"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" wire:loading.remove wire:target="generateBrandAiContent">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                        <span wire:loading wire:target="generateBrandAiContent" class="agricart-category-form__control-action-loading">…</span>
                    </button>
                    @endif
                </div>
                @error('brandForm.english_name') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
            </div>

            <div class="agricart-category-form__field agricart-category-form__field--urdu">
                <label class="agricart-category-form__label agricart-category-form__label--required" for="brand_urdu_name">Urdu Brand Name</label>
                <input
                    id="brand_urdu_name"
                    type="text"
                    class="agricart-category-form__control"
                    placeholder="اردو برانڈ نام"
                    dir="rtl"
                    lang="ur"
                    wire:model="brandForm.urdu_name"
                >
                @error('brandForm.urdu_name') <span class="agricart-category-form__error">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="brand_short_note">Short Note / AI Prompt</label>
        <textarea
            id="brand_short_note"
            class="agricart-category-form__control agricart-category-form__control--textarea"
            rows="2"
            placeholder="e.g. Agricultural machinery manufacturer, Japanese bearing company, Chinese irrigation products…"
            wire:model="brandForm.short_note"
        ></textarea>
        <p class="agricart-category-form__hint">Extra context for AI (products, positioning). The English Brand Name above is always used as the brand identity — you do not need to repeat it here.</p>
    </div>

    @include('filament.catalog.partials.brand-form-assigned-categories-live')

    <div class="agricart-category-form__collapsible">
        <button
            type="button"
            class="agricart-category-form__collapsible-header"
            wire:click="toggleBrandAdditional"
            aria-expanded="{{ $this->brandAdditionalOpen ? 'true' : 'false' }}"
        >
            <span class="agricart-category-form__collapsible-title">Additional Information</span>
            <span @class([
                'agricart-category-form__collapsible-chevron',
                'is-open' => $this->brandAdditionalOpen,
            ]) aria-hidden="true"></span>
        </button>
        <div @class([
            'agricart-category-form__collapsible-body',
            'is-open' => $this->brandAdditionalOpen,
        ])>
            @include('filament.catalog.partials.brand-form-additional-body', [
                'live' => true,
                'brandCodeDisplay' => $this->brandCodeDisplay ?: 'Auto-generated on save',
                'lastAiGeneratedDisplay' => $this->lastAiGeneratedDisplay,
                'countryOptions' => $countryOptions,
            ])
        </div>
    </div>
</div>
@else
<div class="agricart-category-form agricart-brand-form" x-data="agricartBrandForm(@js(\App\Modules\Catalog\Services\BrandManager::categorySelectOptions()))">
    <div class="agricart-category-form__image-row">
        <div class="agricart-category-form__field agricart-brand-form__logo-field">
            <span class="agricart-category-form__label agricart-category-form__label--required">Brand Logo</span>
            <div class="agricart-brand-form__logo-box">
                @include('filament.catalog.partials.brand-form-upload', ['live' => false])
            </div>
            <p class="agricart-category-form__hint">{{ \App\Modules\Catalog\Support\BrandLogoSpec::uploadHint() }}</p>
        </div>

        <div class="agricart-brand-form__names-stack">
            <div class="agricart-category-form__field agricart-category-form__field--english">
                <label class="agricart-category-form__label agricart-category-form__label--required" for="brand_english_name_preview">English Brand Name</label>
                <div class="agricart-category-form__control-wrap">
                    <input
                        id="brand_english_name_preview"
                        type="text"
                        class="agricart-category-form__control agricart-category-form__control--with-action"
                        placeholder="Auto-filled from logo filename"
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
                <label class="agricart-category-form__label agricart-category-form__label--required" for="brand_urdu_name_preview">Urdu Brand Name</label>
                <input
                    id="brand_urdu_name_preview"
                    type="text"
                    class="agricart-category-form__control"
                    placeholder="اردو برانڈ نام"
                    dir="rtl"
                    lang="ur"
                    x-model="urduName"
                >
            </div>
        </div>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="brand_short_note_preview">Short Note / AI Prompt</label>
        <textarea
            id="brand_short_note_preview"
            class="agricart-category-form__control agricart-category-form__control--textarea"
            rows="2"
            placeholder="e.g. Agricultural machinery manufacturer, Japanese bearing company, Chinese irrigation products…"
            x-model="shortNote"
        ></textarea>
        <p class="agricart-category-form__hint">Extra context for AI (products, positioning). The English Brand Name above is always used as the brand identity — you do not need to repeat it here.</p>
    </div>

    @include('filament.catalog.partials.brand-form-assigned-categories-preview')

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
            @include('filament.catalog.partials.brand-form-additional-body', [
                'live' => false,
                'brandCodeDisplay' => $codeDisplay,
                'countryOptions' => $countryOptions,
            ])
        </div>
    </div>
</div>
@endif
