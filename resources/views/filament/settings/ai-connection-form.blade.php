@php
    $modelOptions = $fetchedAiModels ?? [];
    $aiModelSearchOpen = $aiModelSearchOpen ?? false;
    $aiModelSearchQuery = $aiModelSearchQuery ?? '';
    $aiAdvancedOpen = $aiAdvancedOpen ?? false;
@endphp

<div class="agricart-ai-connection-form">
    @if (! empty($connectionTestResult))
        <div @class([
            'agricart-ai-connection-form__test-result',
            'agricart-ai-connection-form__test-result--success' => $connectionTestResult['success'] ?? false,
            'agricart-ai-connection-form__test-result--failed' => ! ($connectionTestResult['success'] ?? false),
        ])>
            <p class="agricart-ai-connection-form__test-result-title">{{ $connectionTestResult['headline'] ?? '' }}</p>
            <dl class="agricart-ai-connection-form__test-result-details">
                @foreach (($connectionTestResult['details'] ?? []) as $label => $value)
                    @if ($label === 'Error' && blank($value))
                        @continue
                    @endif
                    <div class="agricart-ai-connection-form__test-result-row">
                        <dt>{{ $label }}</dt>
                        <dd>{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    <div class="agricart-ai-connection-form__grid agricart-ai-connection-form__grid--2">
        <div class="agricart-ai-connection-form__field">
            <label class="agricart-ai-connection-form__label agricart-ai-connection-form__label--required" for="ai_provider">Provider</label>
            <select id="ai_provider" class="agricart-ai-connection-form__control" wire:model.live="aiConnectionForm.provider">
                @foreach ($providerOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @error('aiConnectionForm.provider') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-ai-connection-form__field">
            <label class="agricart-ai-connection-form__label @if(empty($editingAiConnectionId)) agricart-ai-connection-form__label--required @endif" for="ai_api_key">API Key</label>
            <input
                id="ai_api_key"
                type="password"
                class="agricart-ai-connection-form__control"
                placeholder="{{ ($editingAiConnectionId ?? false) ? 'Leave blank to keep existing key' : 'Enter API key' }}"
                wire:model="aiConnectionForm.api_key"
                autocomplete="new-password"
            >
            @error('aiConnectionForm.api_key') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="agricart-ai-connection-form__field">
        <label class="agricart-ai-connection-form__label agricart-ai-connection-form__label--required" for="ai_base_url">Base URL / Endpoint</label>
        <input id="ai_base_url" type="url" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.base_url">
        @error('aiConnectionForm.base_url') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
    </div>

    <div class="agricart-ai-connection-form__models-row">
        <div class="agricart-ai-connection-form__field agricart-ai-connection-form__field--grow">
            <label class="agricart-ai-connection-form__label agricart-ai-connection-form__label--required">Model</label>
            @include('filament.settings.partials.ai-connection-model-search', [
                'aiConnectionForm' => $aiConnectionForm,
                'fetchedAiModels' => $modelOptions,
                'aiModelSearchOpen' => $aiModelSearchOpen,
                'aiModelSearchQuery' => $aiModelSearchQuery ?? '',
            ])
            @error('aiConnectionForm.model') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
            @if (count($modelOptions) > 0)
                <p class="agricart-ai-connection-form__hint">{{ count($modelOptions) }} models loaded. Click the field to search and select.</p>
            @else
                <p class="agricart-ai-connection-form__hint">Enter your API key, then click Fetch Models.</p>
            @endif
        </div>
        <button
            type="button"
            class="agricart-ai-connection-form__fetch-btn"
            wire:click="fetchAiModels"
            wire:loading.attr="disabled"
            wire:target="fetchAiModels"
        >
            <span wire:loading.remove wire:target="fetchAiModels">Fetch Models</span>
            <span wire:loading wire:target="fetchAiModels">Fetching...</span>
        </button>
    </div>

    <div class="agricart-category-form__collapsible agricart-ai-connection-form__advanced">
        <button
            type="button"
            class="agricart-category-form__collapsible-header"
            wire:click="toggleAiAdvancedSettings"
            aria-expanded="{{ $aiAdvancedOpen ? 'true' : 'false' }}"
        >
            <span class="agricart-category-form__collapsible-title">Advanced Settings</span>
            <span @class([
                'agricart-category-form__collapsible-chevron',
                'is-open' => $aiAdvancedOpen,
            ]) aria-hidden="true"></span>
        </button>
        <div @class([
            'agricart-category-form__collapsible-body',
            'is-open' => $aiAdvancedOpen,
        ])>
            <p class="agricart-ai-connection-form__hint agricart-ai-connection-form__hint--inline">
                Defaults work for most setups. Adjust limits only when needed.
            </p>
            <div class="agricart-ai-connection-form__grid agricart-ai-connection-form__grid--2">
                <div class="agricart-ai-connection-form__field">
                    <label class="agricart-ai-connection-form__label" for="ai_context_window">Context Window</label>
                    <input id="ai_context_window" type="number" min="1024" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.context_window">
                    @error('aiConnectionForm.context_window') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
                </div>
                <div class="agricart-ai-connection-form__field">
                    <label class="agricart-ai-connection-form__label" for="ai_max_output_tokens">Max Output Tokens</label>
                    <input id="ai_max_output_tokens" type="number" min="256" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.max_output_tokens">
                    @error('aiConnectionForm.max_output_tokens') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="agricart-ai-connection-form__grid agricart-ai-connection-form__grid--3">
                <div class="agricart-ai-connection-form__field">
                    <label class="agricart-ai-connection-form__label" for="ai_temperature">Temperature</label>
                    <input id="ai_temperature" type="number" min="0" max="2" step="0.01" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.temperature">
                    @error('aiConnectionForm.temperature') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
                </div>
                <div class="agricart-ai-connection-form__field">
                    <label class="agricart-ai-connection-form__label" for="ai_timeout">Timeout (seconds)</label>
                    <input id="ai_timeout" type="number" min="5" max="300" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.timeout">
                    @error('aiConnectionForm.timeout') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
                </div>
                <div class="agricart-ai-connection-form__field">
                    <label class="agricart-ai-connection-form__label" for="ai_retry_count">Retry Count</label>
                    <input id="ai_retry_count" type="number" min="0" max="5" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.retry_count">
                    @error('aiConnectionForm.retry_count') <span class="agricart-ai-connection-form__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="agricart-ai-connection-form__grid agricart-ai-connection-form__grid--2">
        <div class="agricart-ai-connection-form__field">
            <label class="agricart-ai-connection-form__label" for="ai_is_active">Active</label>
            <select id="ai_is_active" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.is_active">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="agricart-ai-connection-form__field">
            <label class="agricart-ai-connection-form__label" for="ai_is_default">Default Provider</label>
            <select id="ai_is_default" class="agricart-ai-connection-form__control" wire:model="aiConnectionForm.is_default">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
</div>
