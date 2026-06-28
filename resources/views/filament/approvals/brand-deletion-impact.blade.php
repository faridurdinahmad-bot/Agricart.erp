@php
    /** @var \App\Models\Catalog\BrandDeletionRequest|null $request */
    /** @var \App\Models\Catalog\Brand|null $brand */
    /** @var \App\Modules\Catalog\Dto\BrandDeletionImpact|null $impact */
@endphp

<div class="agricart-category-deletion-impact">
    @if ($brand && $impact)
        <div class="agricart-category-deletion-impact__summary">
            <p><strong>{{ $brand->code }}</strong> — {{ $brand->name_en }}</p>
            @if ($request?->reason)
                <p class="agricart-category-deletion-impact__reason"><strong>Request reason:</strong> {{ $request->reason }}</p>
            @endif
            <p class="agricart-category-deletion-impact__meta">
                Requested by {{ $request?->requestedByUser?->name ?? '—' }}
                on {{ $request?->requested_at?->format('d M Y, H:i') ?? '—' }}
            </p>
        </div>

        <div class="agricart-category-deletion-impact__stats">
            <div class="agricart-category-deletion-impact__stat">
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->assignedCategoriesCount }}</span>
                <span class="agricart-category-deletion-impact__stat-label">Assigned categories</span>
            </div>
            <div class="agricart-category-deletion-impact__stat">
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->productsCount }}</span>
                <span class="agricart-category-deletion-impact__stat-label">Products</span>
            </div>
            <div class="agricart-category-deletion-impact__stat">
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->hasAiContent ? 'Yes' : 'No' }}</span>
                <span class="agricart-category-deletion-impact__stat-label">AI content</span>
            </div>
        </div>

        @if ($impact->blockers !== [])
            <div class="agricart-category-deletion-impact__blockers">
                <h4>Cannot approve until resolved</h4>
                <ul>
                    @foreach ($impact->blockers as $blocker)
                        <li>{{ $blocker }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($impact->warnings !== [])
            <div class="agricart-category-deletion-impact__warnings">
                <h4>Related data</h4>
                <ul>
                    @foreach ($impact->warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (($assignedCategories ?? collect())->isNotEmpty())
            <div class="agricart-category-deletion-impact__hierarchy">
                <h4>Assigned Categories</h4>
                <ul>
                    @foreach ($assignedCategories as $category)
                        <li>{{ $category->code }} — {{ $category->name_en }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @else
        <p>Brand details are unavailable for this request.</p>
    @endif
</div>
