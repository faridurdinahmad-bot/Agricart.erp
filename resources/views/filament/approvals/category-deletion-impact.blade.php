@php
    /** @var \App\Models\Catalog\CategoryDeletionRequest|null $request */
    /** @var \App\Models\Catalog\Category|null $category */
    /** @var \App\Modules\Catalog\Dto\CategoryDeletionImpact|null $impact */
@endphp

<div class="agricart-category-deletion-impact">
    @if ($category && $impact)
        <div class="agricart-category-deletion-impact__summary">
            <p><strong>{{ $category->code }}</strong> — {{ $category->name_en }}</p>
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
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->directChildrenCount }}</span>
                <span class="agricart-category-deletion-impact__stat-label">Direct children</span>
            </div>
            <div class="agricart-category-deletion-impact__stat">
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->descendantsCount }}</span>
                <span class="agricart-category-deletion-impact__stat-label">Total descendants</span>
            </div>
            <div class="agricart-category-deletion-impact__stat">
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->productsCount }}</span>
                <span class="agricart-category-deletion-impact__stat-label">Products</span>
            </div>
            <div class="agricart-category-deletion-impact__stat">
                <span class="agricart-category-deletion-impact__stat-value">{{ $impact->redirectsCount }}</span>
                <span class="agricart-category-deletion-impact__stat-label">URL redirects</span>
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

        @if ($hierarchy !== [])
            <div class="agricart-category-deletion-impact__hierarchy">
                <h4>Hierarchy</h4>
                <p>{{ \App\Modules\Catalog\Services\CategoryManager::hierarchyBreadcrumb($hierarchy) }}</p>
            </div>
        @endif
    @else
        <p>Category details are unavailable for this request.</p>
    @endif
</div>
