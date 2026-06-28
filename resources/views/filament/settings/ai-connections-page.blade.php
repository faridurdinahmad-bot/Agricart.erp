<div class="agricart-users-list">
    <div class="agricart-users-list__toolbar agricart-users-list__toolbar--split">
        <div class="agricart-users-list__filters">
            <input
                type="search"
                class="agricart-users-list__filter-input"
                placeholder="Search provider, model, endpoint..."
                wire:model.live.debounce.300ms="search"
            >
            <select class="agricart-users-list__filter-select" wire:model.live="providerFilter">
                <option value="">All Providers</option>
                @foreach ($this->providerFilterOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="statusFilter">
                <option value="">All Statuses</option>
                <option value="connected">Connected</option>
                <option value="disconnected">Disconnected</option>
            </select>
        </div>

        {{ $this->addAiConnectionAction }}
    </div>

    <div class="agricart-users-list__table-wrap" wire:loading.class="agricart-users-list__table-wrap--loading">
        <div class="agricart-users-list__loading" wire:loading.flex wire:target="search,providerFilter,statusFilter,testAiConnection,saveAiConnection">
            Loading AI connections...
        </div>

        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Model</th>
                    <th>Endpoint / Base URL</th>
                    <th>Status</th>
                    <th>Default</th>
                    <th>Last Tested</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->filteredAiConnections as $connection)
                    <tr wire:key="ai-connection-{{ $connection->id }}">
                        <td>{{ $connection->provider->label() }}</td>
                        <td>{{ $connection->model }}</td>
                        <td class="agricart-ai-connections-page__endpoint">{{ $connection->base_url }}</td>
                        <td>
                            <span @class([
                                'agricart-users-list__badge',
                                $connection->statusBadgeClass(),
                            ])>
                                {{ $connection->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            @if ($connection->is_default)
                                <span class="agricart-users-list__badge agricart-users-list__badge--active">Default</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $connection->lastTestedLabel() }}</td>
                        <td class="agricart-users-list__actions-col">
                            <button
                                type="button"
                                class="agricart-users-list__row-btn"
                                wire:click="openEditAiConnection({{ $connection->id }})"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                class="agricart-users-list__row-btn"
                                wire:click="testAiConnection({{ $connection->id }})"
                                wire:loading.attr="disabled"
                                wire:target="testAiConnection({{ $connection->id }})"
                            >
                                Test Connection
                            </button>
                            <button
                                type="button"
                                class="agricart-users-list__row-btn agricart-users-list__row-btn--danger"
                                wire:click="deleteAiConnection({{ $connection->id }})"
                                wire:confirm="Delete this AI connection?"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="agricart-users-list__empty">
                                <p class="agricart-users-list__empty-title">No AI connections found</p>
                                <p class="agricart-users-list__empty-text">Add an AI connection to enable content generation across Agricart.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
