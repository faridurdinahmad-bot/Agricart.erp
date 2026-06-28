@php
    $display = static function (?string $value): string {
        return filled($value) ? $value : '—';
    };

    $hierarchy = $hierarchy ?? [];
    $imageUrl = $imageUrl ?? null;
    $imageDetails = $imageDetails ?? [];
    $promptInfo = $promptInfo ?? [];
    $urlRedirects = $urlRedirects ?? collect();
@endphp

@if (! $category)
    <p class="agricart-category-view__empty">Category not found.</p>
@else
<div class="agricart-category-view" data-agricart-print-document>
    <header class="agricart-print-only agricart-print-header">
        <h1 class="agricart-print-header__title">Category Review Report</h1>
        <p class="agricart-print-header__meta">
            {{ $category->code }} · {{ $category->name_en }} · Printed {{ now()->timezone(config('app.timezone'))->format('d M Y, H:i') }}
        </p>
    </header>

    <div class="agricart-category-view__hero">
        <div class="agricart-category-view__hero-media">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $category->name_en }}" class="agricart-category-view__image">
            @else
                <div class="agricart-category-view__image-placeholder">No image</div>
            @endif
        </div>
        <div class="agricart-category-view__hero-body">
            <p class="agricart-category-view__code">{{ $category->code }}</p>
            <h2 class="agricart-category-view__title">{{ $category->name_en }}</h2>
            <p class="agricart-category-view__title-ur" dir="rtl" lang="ur">{{ $display($category->name_ur) }}</p>
            <div class="agricart-category-view__badges">
                <span @class(['agricart-users-list__badge', 'agricart-users-list__badge--active' => $category->is_active, 'agricart-users-list__badge--inactive' => ! $category->is_active])>
                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span @class(['agricart-users-list__badge', $category->aiStatusBadgeClass()])>{{ $category->aiStatusLabel() }}</span>
            </div>
            @if ($hierarchy !== [])
                <p class="agricart-category-view__breadcrumb">
                    @foreach ($hierarchy as $crumb)
                        <span>{{ $crumb['english_name'] }}</span>@if (! ($crumb['is_current'] ?? false)) › @endif
                    @endforeach
                </p>
            @endif
        </div>
    </div>

    <div class="agricart-category-view__stats">
        <div class="agricart-category-view__stat"><span>Level</span><strong>{{ count($hierarchy) }}</strong></div>
        <div class="agricart-category-view__stat"><span>Children</span><strong>{{ $category->children()->count() }}</strong></div>
        <div class="agricart-category-view__stat"><span>Products</span><strong>0</strong></div>
        <div class="agricart-category-view__stat"><span>Display Order</span><strong>{{ $category->display_order }}</strong></div>
    </div>

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Identity',
        'fields' => [
            'HS Code' => $display($category->hs_code),
            'AI Model' => $display($category->last_ai_model),
            'Last AI Generated' => $category->lastAiGeneratedLabel(),
            'Content Reviewed' => $category->contentReviewedLabel(),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'AI Content',
        'groups' => [
            'Descriptions' => [
                'Short Description (EN)' => $display($category->short_description_en),
                'Short Description (UR)' => $display($category->short_description_ur),
                'Long Description (EN)' => $display($category->long_description_en),
                'Long Description (UR)' => $display($category->long_description_ur),
            ],
            'Usage & Benefits' => [
                'Usage (EN)' => $display($category->usage_en),
                'Usage (UR)' => $display($category->usage_ur),
                'Benefits (EN)' => $display($category->benefits_en),
                'Benefits (UR)' => $display($category->benefits_ur),
                'Warnings (EN)' => $display($category->warnings_en),
                'Warnings (UR)' => $display($category->warnings_ur),
            ],
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'SEO',
        'fields' => [
            'SEO Title' => $display($category->seo_title),
            'Focus Keyword (EN)' => $display($category->seo_focus_keyword_en),
            'Focus Keyword (UR)' => $display($category->seo_focus_keyword_ur),
            'Meta Description' => $display($category->meta_description),
            'Meta Keywords' => $display($category->meta_keywords),
            'URL Slug' => $display($category->url_slug),
            'Canonical URL' => $display($category->canonical_url),
            'Meta Robots' => $display($category->meta_robots),
            'Open Graph Title' => $display($category->og_title),
            'Open Graph Description' => $display($category->og_description),
        ],
    ])

    @if ($urlRedirects->isNotEmpty())
        <section class="agricart-category-view__section">
            <h3 class="agricart-category-view__section-title">URL Redirect History</h3>
            <p class="agricart-category-view__hint">301 redirects recorded when the canonical URL changed.</p>
            <table class="agricart-category-view__redirect-table">
                <thead>
                    <tr>
                        <th>Old URL</th>
                        <th>New URL</th>
                        <th>Changed At</th>
                        <th>Changed By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($urlRedirects as $redirect)
                        <tr>
                            <td>{{ $redirect->old_url }}</td>
                            <td>{{ $redirect->new_url }}</td>
                            <td>{{ $redirect->changed_at?->format('d M Y, H:i') ?? '—' }}</td>
                            <td>{{ $redirect->changedByUser?->name ?? 'System' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Search',
        'fields' => [
            'Synonyms (EN)' => $display($category->synonyms_en),
            'Synonyms (UR)' => $display($category->synonyms_ur),
            'Alternate Spellings' => $display($category->alternate_spellings),
            'Search Aliases' => $display($category->search_aliases),
            'Internal Tags' => $display($category->internal_tags),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Marketplace',
        'fields' => [
            'Google Category' => $display($category->google_category),
            'Facebook Category' => $display($category->facebook_category),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Image Details',
        'fields' => [
            'Filename' => $display($imageDetails['filename'] ?? null),
            'Format' => $display($imageDetails['format'] ?? null),
            'File Size' => $display($imageDetails['size_human'] ?? null),
            'Dimensions' => filled($imageDetails['width'] ?? null)
                ? ($imageDetails['width'].' × '.$imageDetails['height'].' px')
                : '—',
            'Storage Path' => $display($imageDetails['path'] ?? null),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Prompt Information',
        'fields' => [
            'Prompt Template' => $display($promptInfo['template_name'] ?? null),
            'Prompt Version' => $display($promptInfo['template_version'] ?? null),
            'AI Provider' => $display($promptInfo['ai_provider'] ?? null),
            'Model' => $display($promptInfo['model'] ?? null),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'AI Prompt Override',
        'fields' => [
            'Override Instructions' => $display($category->ai_prompt_override),
        ],
    ])

    <footer class="agricart-print-only agricart-print-footer">
        Agricart ERP — Category review report
    </footer>
</div>
@endif
