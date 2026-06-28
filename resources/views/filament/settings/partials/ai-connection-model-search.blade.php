@php
    $aiModelSearchOpen = $aiModelSearchOpen ?? false;
    $aiModelSearchQuery = $aiModelSearchQuery ?? '';
@endphp

<div class="agricart-ai-connection-form__model-search">
    <div
        class="agricart-ai-connection-form__model-trigger"
        role="combobox"
        aria-expanded="{{ $aiModelSearchOpen ? 'true' : 'false' }}"
    >
        <button
            type="button"
            class="agricart-ai-connection-form__model-trigger-main"
            wire:click="toggleAiModelSearch"
            @disabled(! $this->hasFetchedAiModels())
        >
            <span class="agricart-ai-connection-form__model-trigger-label">
                {{ $this->selectedAiModelLabel() }}
            </span>
        </button>
        <span class="agricart-ai-connection-form__model-trigger-actions">
            @if (filled($aiConnectionForm['model'] ?? null))
                <button
                    type="button"
                    class="agricart-ai-connection-form__model-clear"
                    wire:click.stop="clearAiModelSelection"
                    aria-label="Clear selected model"
                >&times;</button>
            @endif
            <span @class([
                'agricart-ai-connection-form__model-chevron',
                'is-open' => $aiModelSearchOpen,
            ]) aria-hidden="true"></span>
        </span>
    </div>

    @if ($aiModelSearchOpen)
        <div class="agricart-ai-connection-form__model-dropdown">
            <input
                type="search"
                class="agricart-ai-connection-form__model-search-input"
                placeholder="Type to search models..."
                wire:model.live.debounce.150ms="aiModelSearchQuery"
                wire:keydown.escape="closeAiModelSearch"
            >
            <div class="agricart-ai-connection-form__model-options">
                @forelse ($this->filteredFetchedAiModels() as $model)
                    <button
                        type="button"
                        wire:key="ai-model-option-{{ md5($model['id']) }}"
                        class="agricart-ai-connection-form__model-option @if((string) ($aiConnectionForm['model'] ?? '') === (string) $model['id']) is-selected @endif"
                        wire:click="selectAiModel(@js($model['id']))"
                    >
                        <span class="agricart-ai-connection-form__model-option-name">{{ $model['name'] ?? $model['id'] }}</span>
                        @if (($model['name'] ?? '') !== ($model['id'] ?? ''))
                            <span class="agricart-ai-connection-form__model-option-id">{{ $model['id'] }}</span>
                        @endif
                    </button>
                @empty
                    <p class="agricart-ai-connection-form__model-empty">
                        @if (filled($aiModelSearchQuery))
                            No models match your search.
                        @else
                            No models loaded yet.
                        @endif
                    </p>
                @endforelse
            </div>
            @if (count($fetchedAiModels ?? []) > (int) config('ai.models.picker_preview_limit', 80) && blank($aiModelSearchQuery))
                <p class="agricart-ai-connection-form__model-more">Showing first {{ (int) config('ai.models.picker_preview_limit', 80) }} models. Type to search all {{ count($fetchedAiModels) }}.</p>
            @endif
        </div>
    @endif
</div>
