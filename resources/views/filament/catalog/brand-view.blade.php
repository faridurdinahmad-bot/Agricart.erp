@php
    use App\Core\Authorization\Enums\PermissionAction;

    $display = static function (?string $value): string {
        return filled($value) ? $value : '—';
    };
@endphp

@if (! $brand)
    <p class="agricart-category-view__empty">Brand not found.</p>
@else
<div class="agricart-category-view agricart-brand-view" data-agricart-print-document>
    <header class="agricart-print-only agricart-print-header">
        <h1 class="agricart-print-header__title">Brand Review Report</h1>
        <p class="agricart-print-header__meta">
            {{ $brand->code }} · {{ $brand->name_en }} · Printed {{ now()->timezone(config('app.timezone'))->format('d M Y, H:i') }}
        </p>
    </header>

    <div class="agricart-category-view__hero">
        <div class="agricart-category-view__hero-media">
            @if ($imageUrl ?? null)
                <img src="{{ $imageUrl }}" alt="{{ $brand->name_en }}" class="agricart-category-view__image">
            @else
                <div class="agricart-category-view__image-placeholder">No logo</div>
            @endif
        </div>
        <div class="agricart-category-view__hero-body">
            <p class="agricart-category-view__code">{{ $brand->code }}</p>
            <h2 class="agricart-category-view__title">{{ $brand->name_en }}</h2>
            <p class="agricart-category-view__title-ur" dir="rtl" lang="ur">{{ $display($brand->name_ur) }}</p>
            <div class="agricart-category-view__badges">
                @if ($brand->isPendingDeletion())
                    <span class="agricart-users-list__badge agricart-users-list__badge--pending">Pending Deletion</span>
                @else
                    <span @class([
                        'agricart-users-list__badge',
                        'agricart-users-list__badge--active' => $brand->is_active,
                        'agricart-users-list__badge--inactive' => ! $brand->is_active,
                    ])>
                        {{ $brand->is_active ? 'Active' : 'Inactive' }}
                    </span>
                @endif
                <span @class(['agricart-users-list__badge', $brand->aiStatusBadgeClass()])>
                    {{ $brand->aiStatusLabel() }}
                </span>
            </div>
            @if (filled($brand->short_note))
                <p class="agricart-brand-view__short-note">{{ $brand->short_note }}</p>
            @endif
        </div>
    </div>

    <div class="agricart-category-view__stats">
        <div class="agricart-category-view__stat"><span>Assigned Categories</span><strong>{{ count($assignedCategories ?? []) }}</strong></div>
        <div class="agricart-category-view__stat"><span>Country</span><strong>{{ $display($brand->country) }}</strong></div>
        <div class="agricart-category-view__stat"><span>Last AI Generated</span><strong>{{ $brand->lastAiGeneratedLabel() }}</strong></div>
        <div class="agricart-category-view__stat"><span>Content Reviewed</span><strong>{{ $brand->contentReviewedLabel() }}</strong></div>
    </div>

    @if (($assignedCategories ?? []) !== [])
        @include('filament.catalog.partials.category-view-section', [
            'title' => 'Assigned Categories',
            'fields' => collect($assignedCategories)->mapWithKeys(fn ($category) => [
                $category['code'] => $category['name_en'],
            ])->all(),
        ])
    @endif

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Identity',
        'fields' => [
            'Short Note / AI Prompt' => $display($brand->short_note),
            'AI Model' => $display($brand->last_ai_model),
            'Last AI Generated' => $brand->lastAiGeneratedLabel(),
            'Content Reviewed' => $brand->contentReviewedLabel(),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Descriptions',
        'groups' => [
            'Content' => [
                'Short Description (EN)' => $display($brand->short_description_en),
                'Short Description (UR)' => $display($brand->short_description_ur),
                'Long Description (EN)' => $display($brand->long_description_en),
                'Long Description (UR)' => $display($brand->long_description_ur),
                'Brand Overview (EN)' => $display($brand->brand_overview_en),
                'Brand Overview (UR)' => $display($brand->brand_overview_ur),
            ],
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'SEO',
        'fields' => [
            'SEO Title' => $display($brand->seo_title),
            'SEO Description' => $display($brand->seo_description),
            'SEO Keywords' => $display($brand->seo_keywords),
        ],
    ])

    @include('filament.catalog.partials.category-view-section', [
        'title' => 'Company Information',
        'fields' => [
            'Country' => $display($brand->country),
            'Website' => $display($brand->website),
        ],
    ])
</div>
@endif
