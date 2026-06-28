@php
    $live = $live ?? false;
    $brandCodeDisplay = $brandCodeDisplay ?? 'BR-1';
    $countryOptions = $countryOptions ?? [
        'Pakistan', 'Japan', 'China', 'Germany', 'United States', 'Sweden', 'Italy', 'United Kingdom', 'India', 'Turkey',
    ];
@endphp

<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">Descriptions</h3>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_short_description_en">Short Description (English)</label>
            <textarea id="brand_short_description_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Brief brand summary" @unless($live) x-model="shortDescriptionEn" @endunless @if($live) wire:model="brandForm.short_description_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_short_description_ur">Short Description (Urdu)</label>
            <textarea id="brand_short_description_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="مختصر تفصیل" dir="rtl" lang="ur" @unless($live) x-model="shortDescriptionUr" @endunless @if($live) wire:model="brandForm.short_description_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_long_description_en">Long Description (English)</label>
            <textarea id="brand_long_description_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="Detailed brand description" @unless($live) x-model="longDescriptionEn" @endunless @if($live) wire:model="brandForm.long_description_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_long_description_ur">Long Description (Urdu)</label>
            <textarea id="brand_long_description_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="تفصیلی وضاحت" dir="rtl" lang="ur" @unless($live) x-model="longDescriptionUr" @endunless @if($live) wire:model="brandForm.long_description_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_overview_en">Brand Overview (English)</label>
            <textarea id="brand_overview_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="Brand story, heritage, and positioning" @unless($live) x-model="brandOverviewEn" @endunless @if($live) wire:model="brandForm.brand_overview_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_overview_ur">Brand Overview (Urdu)</label>
            <textarea id="brand_overview_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="برانڈ کی کہانی" dir="rtl" lang="ur" @unless($live) x-model="brandOverviewUr" @endunless @if($live) wire:model="brandForm.brand_overview_ur" @endif></textarea>
        </div>
    </div>
</section>

<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">SEO</h3>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="brand_seo_title">SEO Title</label>
        <input id="brand_seo_title" type="text" class="agricart-category-form__control" placeholder="Page title for search engines" @unless($live) x-model="seoTitle" @endunless @if($live) wire:model="brandForm.seo_title" @endif>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="brand_seo_description">SEO Description</label>
        <textarea id="brand_seo_description" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Meta description for search results" @unless($live) x-model="seoDescription" @endunless @if($live) wire:model="brandForm.seo_description" @endif></textarea>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="brand_seo_keywords">SEO Keywords</label>
        <input id="brand_seo_keywords" type="text" class="agricart-category-form__control" placeholder="Comma-separated keywords" @unless($live) x-model="seoKeywords" @endunless @if($live) wire:model="brandForm.seo_keywords" @endif>
    </div>
</section>

<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">Company Information</h3>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_country">Country</label>
            <select id="brand_country" class="agricart-category-form__control" @unless($live) x-model="country" @endunless @if($live) wire:model="brandForm.country" @endif>
                <option value="">Select country</option>
                @foreach ($countryOptions as $countryOption)
                    <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                @endforeach
            </select>
        </div>
        @include('filament.catalog.partials.brand-form-website-field', [
            'live' => $live,
            'websiteProtocol' => $live ? null : ($websiteProtocol ?? null),
            'websiteDomain' => $live ? null : ($websiteDomain ?? null),
        ])
    </div>
</section>

<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">System</h3>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_code">Brand Code</label>
            <input
                id="brand_code"
                type="text"
                class="agricart-category-form__control agricart-category-form__control--readonly"
                readonly
                disabled
                @if($live)
                    value="{{ $brandCodeDisplay }}"
                @else
                    value="{{ $brandCodeDisplay }}"
                @endif
            >
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="brand_is_active">Status</label>
            <select id="brand_is_active" class="agricart-category-form__control" @unless($live) x-model="isActive" @endunless @if($live) wire:model="brandForm.is_active" @endif>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="brand_last_ai_generated">Last AI Generated</label>
        <input
            id="brand_last_ai_generated"
            type="text"
            class="agricart-category-form__control agricart-category-form__control--readonly"
            readonly
            disabled
            @if($live)
                value="{{ $lastAiGeneratedDisplay ?? 'Not generated yet' }}"
            @else
                x-bind:value="lastAiGenerated || 'Not generated yet'"
            @endif
        >
        <p class="agricart-category-form__hint">Shows when AI content was last generated or refreshed.</p>
    </div>
</section>
