<div class="agricart-users-list">
    <div class="agricart-users-list__toolbar agricart-users-list__toolbar--split">
        <div class="agricart-users-list__filters">
            <input
                type="search"
                class="agricart-users-list__filter-input"
                placeholder="Search model, user, error..."
                wire:model.live.debounce.300ms="search"
            >
            <select class="agricart-users-list__filter-select" wire:model.live="providerFilter">
                <option value="">All Providers</option>
                @foreach ($this->providerFilterOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="taskTypeFilter">
                <option value="">All Task Types</option>
                @foreach ($this->taskTypeFilterOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="targetModuleFilter">
                <option value="">All Modules</option>
                @foreach ($this->targetModuleFilterOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="statusFilter">
                <option value="">All Results</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>

    <div class="agricart-users-list__table-wrap" wire:loading.class="agricart-users-list__table-wrap--loading">
        <div class="agricart-users-list__loading" wire:loading.flex wire:target="search,providerFilter,taskTypeFilter,targetModuleFilter,statusFilter,gotoPage,previousPage,nextPage">
            Loading AI history...
        </div>

        <table class="agricart-users-list__table agricart-ai-logs-page__table">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>User</th>
                    <th>Provider</th>
                    <th>Model</th>
                    <th>Task Type</th>
                    <th>Module</th>
                    <th>Result</th>
                    <th>Response</th>
                    <th>Tokens</th>
                    <th>Cost</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->aiJobHistories as $job)
                    <tr wire:key="ai-job-{{ $job->id }}">
                        <td>{{ $job->createdAtLabel() }}</td>
                        <td>{{ $job->user?->name ?? 'System' }}</td>
                        <td>{{ $job->provider->label() }}</td>
                        <td>{{ $job->model }}</td>
                        <td>{{ $job->task_type->label() }}</td>
                        <td>{{ $job->target_module->label() }}</td>
                        <td>
                            <span @class(['agricart-users-list__badge', $job->statusBadgeClass()])>
                                {{ $job->statusLabel() }}
                            </span>
                        </td>
                        <td>{{ $job->response_time_ms }} ms</td>
                        <td>{{ $job->tokensLabel() }}</td>
                        <td>{{ $job->estimatedCostLabel() }}</td>
                        <td class="agricart-ai-logs-page__error" title="{{ $job->error_message }}">
                            {{ $job->error_message ? \Illuminate\Support\Str::limit($job->error_message, 60) : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="agricart-users-list__empty">
                                <p class="agricart-users-list__empty-title">No AI jobs recorded yet</p>
                                <p class="agricart-users-list__empty-text">Connection tests and module AI tasks will appear here for debugging and billing analysis.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->aiJobHistories->total() > 0)
        <div class="agricart-users-list__pagination">
            <span class="agricart-users-list__pagination-summary">
                Showing {{ $this->aiJobHistories->firstItem() }}–{{ $this->aiJobHistories->lastItem() }} of {{ $this->aiJobHistories->total() }}
            </span>
            <div class="agricart-users-list__pagination-actions">
                @if ($this->aiJobHistories->onFirstPage())
                    <button type="button" class="agricart-users-list__pagination-btn" disabled>Previous</button>
                @else
                    <button type="button" class="agricart-users-list__pagination-btn" wire:click="previousPage">Previous</button>
                @endif
                <span class="agricart-users-list__pagination-page">
                    Page {{ $this->aiJobHistories->currentPage() }} of {{ $this->aiJobHistories->lastPage() }}
                </span>
                @if ($this->aiJobHistories->hasMorePages())
                    <button type="button" class="agricart-users-list__pagination-btn" wire:click="nextPage">Next</button>
                @else
                    <button type="button" class="agricart-users-list__pagination-btn" disabled>Next</button>
                @endif
            </div>
        </div>
    @endif
</div>
