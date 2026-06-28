<div class="agricart-users-list">
    <div class="agricart-users-list__toolbar agricart-users-list__toolbar--split">
        <div class="agricart-users-list__filters">
            <input
                type="search"
                class="agricart-users-list__filter-input"
                placeholder="Search name, code..."
                wire:model.live.debounce.300ms="search"
            >
            <select class="agricart-users-list__filter-select" wire:model.live="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending_deletion">Pending Deletion</option>
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="aiStatusFilter">
                <option value="">All AI Statuses</option>
                <option value="needs_attention">Needs AI Attention</option>
                @foreach (\App\Core\Ai\Enums\AiContentStatus::options() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>

        {{ $this->addBrandAction }}
    </div>

    <div class="agricart-users-list__table-wrap" wire:loading.class="agricart-users-list__table-wrap--loading">
        <div class="agricart-users-list__loading" wire:loading.flex wire:target="search,statusFilter,aiStatusFilter,brandsPage,openEditBrand,saveBrand,approveBrandContentReview">
            Loading brands...
        </div>

        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Brand</th>
                    <th>Urdu Name</th>
                    <th>Status</th>
                    <th>AI Status</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->paginatedBrands as $brand)
                    <tr wire:key="brand-{{ $brand->id }}">
                        <td>{{ $brand->code }}</td>
                        <td>
                            <span class="agricart-users-list__tree-name agricart-users-list__tree-name--depth-0">
                                @if ($logoUrl = \App\Modules\Catalog\Services\BrandImageStorage::url($brand->logo_path))
                                    <img
                                        src="{{ $logoUrl }}"
                                        alt=""
                                        class="agricart-users-list__tree-thumb"
                                    >
                                @endif
                                {{ $brand->name_en }}
                            </span>
                        </td>
                        <td dir="rtl" lang="ur">{{ $brand->name_ur }}</td>
                        <td>
                            @if ($brand->isPendingDeletion())
                                <span class="agricart-users-list__badge agricart-users-list__badge--pending">
                                    Pending Deletion
                                </span>
                            @else
                                <span @class([
                                    'agricart-users-list__badge',
                                    'agricart-users-list__badge--active' => $brand->is_active,
                                    'agricart-users-list__badge--inactive' => ! $brand->is_active,
                                ])>
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span @class(['agricart-users-list__badge', $brand->aiStatusBadgeClass()])>
                                {{ $brand->aiStatusLabel() }}
                            </span>
                        </td>
                        <td class="agricart-users-list__actions-col">
                            @include('filament.catalog.partials.brand-row-actions', [
                                'brandId' => $brand->id,
                                'awaitingReview' => $brand->awaitingContentReview(),
                                'isPendingDeletion' => $brand->isPendingDeletion(),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="agricart-users-list__empty">
                                <p class="agricart-users-list__empty-title">No brands found</p>
                                <p class="agricart-users-list__empty-text">Try adjusting your search or filters, or add a new brand.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->brandListMeta['total'] > 0)
        <div class="agricart-users-list__pagination">
            <span class="agricart-users-list__pagination-summary">
                Showing {{ $this->brandListMeta['from'] }}–{{ $this->brandListMeta['to'] }} of {{ $this->brandListMeta['total'] }}
            </span>
            <div class="agricart-users-list__pagination-actions">
                <button
                    type="button"
                    class="agricart-users-list__pagination-btn"
                    wire:click="goToBrandsPage({{ $this->brandListMeta['currentPage'] - 1 }})"
                    @disabled($this->brandListMeta['currentPage'] <= 1)
                >
                    Previous
                </button>
                <span class="agricart-users-list__pagination-page">
                    Page {{ $this->brandListMeta['currentPage'] }} of {{ $this->brandListMeta['lastPage'] }}
                </span>
                <button
                    type="button"
                    class="agricart-users-list__pagination-btn"
                    wire:click="goToBrandsPage({{ $this->brandListMeta['currentPage'] + 1 }})"
                    @disabled($this->brandListMeta['currentPage'] >= $this->brandListMeta['lastPage'])
                >
                    Next
                </button>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
