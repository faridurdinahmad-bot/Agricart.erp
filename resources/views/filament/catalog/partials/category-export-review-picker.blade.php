@php
    use App\Core\Authorization\Enums\PermissionAction;
@endphp

<div class="agricart-export-review-picker">
    <p class="agricart-export-review-picker__intro">
        Use browser print for the recommended review document, or download machine-readable exports for AI tools and auditors.
    </p>

    <div class="agricart-export-review-picker__actions agricart-export-review-picker__actions--three">
        @if ($this->canPageAction(PermissionAction::Print))
            <button
                type="button"
                class="agricart-export-review-picker__btn agricart-export-review-picker__btn--primary"
                wire:click="printCategoryReviewFromExport"
            >
                <span class="agricart-export-review-picker__btn-title">🖨 Print Review (Recommended)</span>
                <span class="agricart-export-review-picker__btn-meta">Browser print → Save as PDF or paper</span>
            </button>
        @endif

        @if ($this->canPageAction(PermissionAction::Export))
            <button
                type="button"
                class="agricart-export-review-picker__btn"
                wire:click="downloadCategoryContentAudit('pdf')"
            >
                <span class="agricart-export-review-picker__btn-title">📄 Export Audit PDF</span>
                <span class="agricart-export-review-picker__btn-meta">.pdf — standalone audit file</span>
            </button>

            <button
                type="button"
                class="agricart-export-review-picker__btn"
                wire:click="downloadCategoryContentAudit('json')"
            >
                <span class="agricart-export-review-picker__btn-title">📦 Export JSON</span>
                <span class="agricart-export-review-picker__btn-meta">.json — structured audit data</span>
            </button>
        @endif
    </div>
</div>
