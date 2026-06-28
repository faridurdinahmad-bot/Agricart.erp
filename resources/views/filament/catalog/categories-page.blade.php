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
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="aiStatusFilter">
                <option value="">All AI Statuses</option>
                <option value="needs_attention">Needs AI Attention</option>
                @foreach (\App\Core\Ai\Enums\AiContentStatus::options() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>

        {{ $this->addCategoryAction }}
    </div>

    <div class="agricart-users-list__table-wrap" wire:loading.class="agricart-users-list__table-wrap--loading">
        <div class="agricart-users-list__loading" wire:loading.flex wire:target="search,statusFilter,aiStatusFilter,categoriesPage,openEditCategory,saveCategory,approveCategoryContentReview">
            Loading categories...
        </div>

        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Category</th>
                    <th>Urdu Name</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>AI Status</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->paginatedCategories as $row)
                    @php($category = $row['category'])
                    <tr wire:key="category-{{ $category->id }}">
                        <td>{{ $category->code }}</td>
                        <td>
                            <span @class([
                                'agricart-users-list__tree-name',
                                'agricart-users-list__tree-name--depth-'.$row['depth'],
                            ])>
                                @if ($category->image_path)
                                    <img
                                        src="{{ \App\Modules\Catalog\Services\CategoryImageStorage::url($category->image_path) }}"
                                        alt=""
                                        class="agricart-users-list__tree-thumb"
                                    >
                                @endif
                                {{ $category->name_en }}
                            </span>
                        </td>
                        <td dir="rtl" lang="ur">{{ $category->name_ur }}</td>
                        <td>{{ $category->display_order }}</td>
                        <td>
                            <span @class([
                                'agricart-users-list__badge',
                                'agricart-users-list__badge--active' => $category->is_active,
                                'agricart-users-list__badge--inactive' => ! $category->is_active,
                            ])>
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span @class(['agricart-users-list__badge', $category->aiStatusBadgeClass()])>
                                {{ $category->aiStatusLabel() }}
                            </span>
                        </td>
                        <td class="agricart-users-list__actions-col">
                            @include('filament.catalog.partials.category-row-actions', [
                                'categoryId' => $category->id,
                                'awaitingReview' => $category->awaitingContentReview(),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="agricart-users-list__empty">
                                <p class="agricart-users-list__empty-title">No categories found</p>
                                <p class="agricart-users-list__empty-text">Try adjusting your search or filters, or add a new category.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->categoryListMeta['total'] > 0)
        <div class="agricart-users-list__pagination">
            <span class="agricart-users-list__pagination-summary">
                Showing {{ $this->categoryListMeta['from'] }}–{{ $this->categoryListMeta['to'] }} of {{ $this->categoryListMeta['total'] }}
            </span>
            <div class="agricart-users-list__pagination-actions">
                <button
                    type="button"
                    class="agricart-users-list__pagination-btn"
                    wire:click="goToCategoriesPage({{ $this->categoryListMeta['currentPage'] - 1 }})"
                    @disabled($this->categoryListMeta['currentPage'] <= 1)
                >
                    Previous
                </button>
                <span class="agricart-users-list__pagination-page">
                    Page {{ $this->categoryListMeta['currentPage'] }} of {{ $this->categoryListMeta['lastPage'] }}
                </span>
                <button
                    type="button"
                    class="agricart-users-list__pagination-btn"
                    wire:click="goToCategoriesPage({{ $this->categoryListMeta['currentPage'] + 1 }})"
                    @disabled($this->categoryListMeta['currentPage'] >= $this->categoryListMeta['lastPage'])
                >
                    Next
                </button>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
