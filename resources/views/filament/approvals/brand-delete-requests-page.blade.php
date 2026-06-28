<div class="agricart-users-list">
    <div class="agricart-users-list__table-wrap">
        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Brand</th>
                    <th>Requested By</th>
                    <th>Requested At</th>
                    <th>Categories</th>
                    <th>Products</th>
                    <th>Reason</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->pendingDeletionRequests as $request)
                    @php($brand = $request->brand)
                    @php($impact = $brand ? \App\Modules\Catalog\Services\BrandDeletionImpactAnalyzer::analyze($brand) : null)
                    <tr wire:key="brand-deletion-request-{{ $request->id }}">
                        <td>{{ $brand?->code ?? '—' }}</td>
                        <td>{{ $brand?->name_en ?? '—' }}</td>
                        <td>{{ $request->requestedByUser?->name ?? '—' }}</td>
                        <td>{{ $request->requested_at?->format('d M Y, H:i') }}</td>
                        <td>{{ $impact?->assignedCategoriesCount ?? '—' }}</td>
                        <td>{{ $impact?->productsCount ?? '—' }}</td>
                        <td>{{ filled($request->reason) ? \Illuminate\Support\Str::limit($request->reason, 80) : '—' }}</td>
                        <td class="agricart-users-list__actions-col">
                            @include('filament.approvals.partials.brand-deletion-row-actions', [
                                'requestId' => $request->id,
                                'canApprove' => (bool) $impact?->canApprove,
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="agricart-users-list__empty">
                                <p class="agricart-users-list__empty-title">No pending brand deletion requests</p>
                                <p class="agricart-users-list__empty-text">Deletion requests submitted from Catalog → Brands will appear here.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
