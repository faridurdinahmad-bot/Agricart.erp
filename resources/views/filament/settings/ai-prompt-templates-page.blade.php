<div class="agricart-users-list">
    <div class="agricart-users-list__toolbar agricart-users-list__toolbar--split">
        <div class="agricart-users-list__filters">
            <input
                type="search"
                class="agricart-users-list__filter-input"
                placeholder="Search prompt name, module, task type..."
                wire:model.live.debounce.300ms="search"
            >
            <select class="agricart-users-list__filter-select" wire:model.live="moduleFilter">
                <option value="">All Modules</option>
                @foreach ($this->aiPromptTemplateModuleOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="taskTypeFilter">
                <option value="">All Task Types</option>
                @foreach ($this->aiPromptTemplateTaskTypeOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            <select class="agricart-users-list__filter-select" wire:model.live="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        {{ $this->addAiPromptTemplateAction }}
    </div>

    <div class="agricart-users-list__table-wrap" wire:loading.class="agricart-users-list__table-wrap--loading">
        <div class="agricart-users-list__loading" wire:loading.flex wire:target="search,moduleFilter,taskTypeFilter,statusFilter,saveAiPromptTemplate">
            Loading prompt templates...
        </div>

        <table class="agricart-users-list__table">
            <thead>
                <tr>
                    <th>Prompt Name</th>
                    <th>Module</th>
                    <th>Task Type</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th class="agricart-users-list__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->filteredAiPromptTemplates as $template)
                    <tr wire:key="ai-prompt-template-{{ $template->id }}">
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->target_module->label() }}</td>
                        <td>{{ $template->task_type->label() }}</td>
                        <td>
                            <span @class([
                                'agricart-users-list__badge',
                                $template->statusBadgeClass(),
                            ])>
                                {{ $template->statusLabel() }}
                            </span>
                        </td>
                        <td>{{ $template->updatedLabel() }}</td>
                        <td class="agricart-users-list__actions-col">
                            <button
                                type="button"
                                class="agricart-users-list__row-btn"
                                wire:click="openEditAiPromptTemplate({{ $template->id }})"
                            >
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="agricart-users-list__empty">
                                <p class="agricart-users-list__empty-title">No prompt templates found</p>
                                <p class="agricart-users-list__empty-text">Add a prompt template or run the AI prompt seeder to create the default placeholders.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-filament-actions::modals />
</div>
