<div class="agricart-category-form-modal">
    <div class="agricart-category-form-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
        <header class="agricart-category-form-modal__header">
            <h2 class="agricart-category-form-modal__title" id="category-modal-title">Add Category</h2>
            <button type="button" class="agricart-category-form-modal__close" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div class="agricart-category-form-modal__body">
            @include('filament.catalog.category-form', ['live' => false])
        </div>

        <footer class="agricart-category-form-modal__footer">
            <button type="button" class="agricart-category-form-modal__btn agricart-category-form-modal__btn--primary">Save &amp; Close</button>
            <button type="button" class="agricart-category-form-modal__btn agricart-category-form-modal__btn--secondary">Save &amp; Add Next</button>
            <button type="button" class="agricart-category-form-modal__btn agricart-category-form-modal__btn--secondary">Cancel</button>
        </footer>
    </div>
</div>
