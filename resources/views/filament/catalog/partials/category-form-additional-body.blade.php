{{-- Basic details (fast entry extras) --}}
@if ($live ?? false)
    @php
        $categoryCodeDisplay = $this->categoryCodeDisplay;
        $categorySlugDisplay = $this->categorySlugDisplay;
        $categoryCanonicalDisplay = $this->categoryCanonicalDisplay;
        $lastAiGeneratedDisplay = $this->lastAiGeneratedDisplay;
        $categoryAiPromptOpen = $this->categoryAiPromptOpen;
    @endphp
@endif
<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">Basic Details</h3>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_hs_code">HS Code</label>
            <input id="category_hs_code" type="text" class="agricart-category-form__control" placeholder="e.g. 8424.81" @unless($live ?? false) x-model="hsCode" @endunless @if($live ?? false) wire:model="categoryForm.hs_code" @endif>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_display_order">Display Order</label>
            <input id="category_display_order" type="number" min="0" class="agricart-category-form__control" placeholder="0" @unless($live ?? false) x-model="displayOrder" @endunless @if($live ?? false) wire:model="categoryForm.display_order" @endif>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_code">Category Code</label>
            <input
                id="category_code"
                type="text"
                class="agricart-category-form__control agricart-category-form__control--readonly"
                readonly
                disabled
                @if($live ?? false)
                    value="{{ $categoryCodeDisplay ?? 'Auto-generated on save' }}"
                @else
                    value="Auto-generated on save"
                @endif
            >
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_is_active">Status</label>
            <select id="category_is_active" class="agricart-category-form__control" @unless($live ?? false) x-model="isActive" @endunless @if($live ?? false) wire:model="categoryForm.is_active" @endif>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>
</section>

{{-- AI Content --}}
<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">AI Content</h3>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_short_description_en">Short Description (English)</label>
            <textarea id="category_short_description_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Brief category summary" @unless($live ?? false) x-model="shortDescriptionEn" @endunless @if($live ?? false) wire:model="categoryForm.short_description_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_short_description_ur">Short Description (Urdu)</label>
            <textarea id="category_short_description_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="مختصر تفصیل" dir="rtl" lang="ur" @unless($live ?? false) x-model="shortDescriptionUr" @endunless @if($live ?? false) wire:model="categoryForm.short_description_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_long_description_en">Long Description (English)</label>
            <textarea id="category_long_description_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="Detailed category description" @unless($live ?? false) x-model="longDescriptionEn" @endunless @if($live ?? false) wire:model="categoryForm.long_description_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_long_description_ur">Long Description (Urdu)</label>
            <textarea id="category_long_description_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="تفصیلی وضاحت" dir="rtl" lang="ur" @unless($live ?? false) x-model="longDescriptionUr" @endunless @if($live ?? false) wire:model="categoryForm.long_description_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_usage_en">Usage / Applications (English)</label>
            <textarea id="category_usage_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Where and how this category is used" @unless($live ?? false) x-model="usageEn" @endunless @if($live ?? false) wire:model="categoryForm.usage_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_usage_ur">Usage / Applications (Urdu)</label>
            <textarea id="category_usage_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="استعمال / اطلاق" dir="rtl" lang="ur" @unless($live ?? false) x-model="usageUr" @endunless @if($live ?? false) wire:model="categoryForm.usage_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_benefits_en">Benefits (English)</label>
            <textarea id="category_benefits_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Key benefits of products in this category" @unless($live ?? false) x-model="benefitsEn" @endunless @if($live ?? false) wire:model="categoryForm.benefits_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_benefits_ur">Benefits (Urdu)</label>
            <textarea id="category_benefits_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="فوائد" dir="rtl" lang="ur" @unless($live ?? false) x-model="benefitsUr" @endunless @if($live ?? false) wire:model="categoryForm.benefits_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_warnings_en">Warnings / Precautions (English)</label>
            <textarea id="category_warnings_en" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Safety notes and precautions" @unless($live ?? false) x-model="warningsEn" @endunless @if($live ?? false) wire:model="categoryForm.warnings_en" @endif></textarea>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_warnings_ur">Warnings / Precautions (Urdu)</label>
            <textarea id="category_warnings_ur" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="انتباہات / احتیاط" dir="rtl" lang="ur" @unless($live ?? false) x-model="warningsUr" @endunless @if($live ?? false) wire:model="categoryForm.warnings_ur" @endif></textarea>
        </div>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="category_last_ai_generated">Last AI Generated</label>
        <input
            id="category_last_ai_generated"
            type="text"
            class="agricart-category-form__control agricart-category-form__control--readonly"
            readonly
            disabled
            @if($live ?? false)
                value="{{ $lastAiGeneratedDisplay ?? 'Not generated yet' }}"
            @else
                x-bind:value="lastAiGenerated || 'Not generated yet'"
            @endif
        >
        <p class="agricart-category-form__hint">Shows when AI content was last generated or refreshed.</p>
    </div>
</section>

{{-- SEO --}}
<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">SEO</h3>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="category_seo_title">SEO Title</label>
        <input id="category_seo_title" type="text" class="agricart-category-form__control" placeholder="Page title for search engines" @unless($live ?? false) x-model="seoTitle" @endunless @if($live ?? false) wire:model="categoryForm.seo_title" @endif>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_seo_focus_keyword_en">SEO Focus Keyword (English)</label>
            <input id="category_seo_focus_keyword_en" type="text" class="agricart-category-form__control" placeholder="Primary SEO keyword" @unless($live ?? false) x-model="seoFocusKeywordEn" @endunless @if($live ?? false) wire:model="categoryForm.seo_focus_keyword_en" @endif>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_seo_focus_keyword_ur">SEO Focus Keyword (Urdu)</label>
            <input id="category_seo_focus_keyword_ur" type="text" class="agricart-category-form__control" placeholder="مرکزی SEO کلیدی لفظ" dir="rtl" lang="ur" @unless($live ?? false) x-model="seoFocusKeywordUr" @endunless @if($live ?? false) wire:model="categoryForm.seo_focus_keyword_ur" @endif>
        </div>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="category_meta_description">Meta Description</label>
        <textarea id="category_meta_description" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Search engine description" @unless($live ?? false) x-model="metaDescription" @endunless @if($live ?? false) wire:model="categoryForm.meta_description" @endif></textarea>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="category_meta_keywords">Meta Keywords</label>
        <input id="category_meta_keywords" type="text" class="agricart-category-form__control" placeholder="keyword-one, keyword-two" @unless($live ?? false) x-model="metaKeywords" @endunless @if($live ?? false) wire:model="categoryForm.meta_keywords" @endif>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_url_slug">URL Slug</label>
            <input
                id="category_url_slug"
                type="text"
                class="agricart-category-form__control agricart-category-form__control--readonly"
                readonly
                disabled
                @if($live ?? false)
                    value="{{ $categorySlugDisplay ?? 'Generated automatically on save' }}"
                @else
                    value="Generated automatically on save"
                @endif
            >
            <p class="agricart-category-form__hint">System-generated from the English name when saved.</p>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_canonical_url">Canonical URL</label>
            <input
                id="category_canonical_url"
                type="text"
                class="agricart-category-form__control agricart-category-form__control--readonly"
                readonly
                disabled
                @if($live ?? false)
                    value="{{ $categoryCanonicalDisplay ?? 'Generated automatically on save' }}"
                @else
                    value="Generated automatically on save"
                @endif
            >
            <p class="agricart-category-form__hint">System-generated storefront URL. Never typed manually.</p>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_meta_robots">Meta Robots</label>
            <select id="category_meta_robots" class="agricart-category-form__control" @unless($live ?? false) x-model="metaRobots" @endunless @if($live ?? false) wire:model="categoryForm.meta_robots" @endif>
                <option value="index, follow">index, follow</option>
                <option value="noindex, follow">noindex, follow</option>
                <option value="index, nofollow">index, nofollow</option>
                <option value="noindex, nofollow">noindex, nofollow</option>
            </select>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_og_title">Open Graph Title</label>
            <input id="category_og_title" type="text" class="agricart-category-form__control" placeholder="Social share title" @unless($live ?? false) x-model="ogTitle" @endunless @if($live ?? false) wire:model="categoryForm.og_title" @endif>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_og_description">Open Graph Description</label>
            <textarea id="category_og_description" class="agricart-category-form__control agricart-category-form__control--textarea" rows="2" placeholder="Social share description" @unless($live ?? false) x-model="ogDescription" @endunless @if($live ?? false) wire:model="categoryForm.og_description" @endif></textarea>
        </div>
    </div>
</section>

{{-- Search & AI --}}
<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">Search &amp; AI</h3>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_synonyms_en">Synonyms (English)</label>
            <input id="category_synonyms_en" type="text" class="agricart-category-form__control" placeholder="sprayer, spray pump" @unless($live ?? false) x-model="synonymsEn" @endunless @if($live ?? false) wire:model="categoryForm.synonyms_en" @endif>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_synonyms_ur">Synonyms (Urdu)</label>
            <input id="category_synonyms_ur" type="text" class="agricart-category-form__control" placeholder="مسنگل، سپرے" dir="rtl" lang="ur" @unless($live ?? false) x-model="synonymsUr" @endunless @if($live ?? false) wire:model="categoryForm.synonyms_ur" @endif>
        </div>
    </div>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_alternate_spellings">Alternate Spellings</label>
            <input id="category_alternate_spellings" type="text" class="agricart-category-form__control" placeholder="sprayers, spryers" @unless($live ?? false) x-model="alternateSpellings" @endunless @if($live ?? false) wire:model="categoryForm.alternate_spellings" @endif>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_search_aliases">Search Aliases</label>
            <input id="category_search_aliases" type="text" class="agricart-category-form__control" placeholder="field sprayer, knapsack" @unless($live ?? false) x-model="searchAliases" @endunless @if($live ?? false) wire:model="categoryForm.search_aliases" @endif>
        </div>
    </div>

    <div class="agricart-category-form__field">
        <label class="agricart-category-form__label" for="category_internal_tags">Internal Tags</label>
        <input id="category_internal_tags" type="text" class="agricart-category-form__control" placeholder="seasonal, high-margin, import" @unless($live ?? false) x-model="internalTags" @endunless @if($live ?? false) wire:model="categoryForm.internal_tags" @endif>
        <p class="agricart-category-form__hint">Not public. Used for internal search and reporting.</p>
    </div>

    <div class="agricart-category-form__nested-collapsible">
        @if ($live ?? false)
            <button
                type="button"
                class="agricart-category-form__nested-collapsible-header"
                wire:click="toggleCategoryAiPrompt"
                aria-expanded="{{ ($categoryAiPromptOpen ?? false) ? 'true' : 'false' }}"
            >
                <span class="agricart-category-form__nested-collapsible-title">AI Prompt Override</span>
                <span @class([
                    'agricart-category-form__collapsible-chevron',
                    'is-open' => $categoryAiPromptOpen ?? false,
                ]) aria-hidden="true"></span>
            </button>
            <div @class([
                'agricart-category-form__nested-collapsible-body',
                'is-open' => $categoryAiPromptOpen ?? false,
            ])>
        @else
            <button
                type="button"
                class="agricart-category-form__nested-collapsible-header"
                x-on:click.stop="aiPromptOpen = ! aiPromptOpen"
                x-bind:aria-expanded="aiPromptOpen"
            >
                <span class="agricart-category-form__nested-collapsible-title">AI Prompt Override</span>
                <span class="agricart-category-form__collapsible-chevron" x-bind:class="{ 'is-open': aiPromptOpen }" aria-hidden="true"></span>
            </button>
            <div class="agricart-category-form__nested-collapsible-body" x-bind:class="{ 'is-open': aiPromptOpen }">
        @endif
            <div class="agricart-category-form__field">
                <label class="agricart-category-form__label" for="category_ai_prompt_override">Custom AI Instructions</label>
                <textarea id="category_ai_prompt_override" class="agricart-category-form__control agricart-category-form__control--textarea" rows="3" placeholder="Optional instructions for AI content generation for this category" @unless($live ?? false) x-model="aiPromptOverride" @endunless @if($live ?? false) wire:model="categoryForm.ai_prompt_override" @endif></textarea>
            </div>
        </div>
    </div>
</section>

{{-- Marketplace Ready --}}
<section class="agricart-category-form__subsection">
    <h3 class="agricart-category-form__subsection-title">Marketplace Ready</h3>
    <p class="agricart-category-form__subsection-note">Reserved for future Google and Facebook catalog integrations.</p>

    <div class="agricart-category-form__grid agricart-category-form__grid--2">
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_google_category">Google Category</label>
            <input id="category_google_category" type="text" class="agricart-category-form__control agricart-category-form__control--reserved" placeholder="Coming soon" disabled>
        </div>
        <div class="agricart-category-form__field">
            <label class="agricart-category-form__label" for="category_facebook_category">Facebook Category</label>
            <input id="category_facebook_category" type="text" class="agricart-category-form__control agricart-category-form__control--reserved" placeholder="Coming soon" disabled>
        </div>
    </div>
</section>
