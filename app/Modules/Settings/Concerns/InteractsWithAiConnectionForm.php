<?php

namespace App\Modules\Settings\Concerns;

use App\Core\Ai\Dto\AiTestResult;
use App\Core\Ai\Enums\AiProvider;
use App\Core\Ai\Rules\PublicAiEndpointUrl;
use App\Core\Ai\Services\AiConnectionManager;
use App\Core\Ai\Services\AiService;
use App\Core\Ai\Support\AiConfig;
use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Ai\AiConnection;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait InteractsWithAiConnectionForm
{
    public ?int $editingAiConnectionId = null;

    /** @var array<string, mixed> */
    public array $aiConnectionForm = [];

    /** @var list<array{id: string, name: string, context_window: int|null, max_output_tokens: int|null}> */
    public array $fetchedAiModels = [];

    /** @var array<string, mixed>|null */
    public ?array $connectionTestResult = null;

    public bool $aiModelSearchOpen = false;

    public string $aiModelSearchQuery = '';

    public bool $aiAdvancedOpen = false;

    public function resetAiConnectionForm(): void
    {
        $this->editingAiConnectionId = null;
        $this->aiConnectionForm = self::emptyAiConnectionForm();
        $this->fetchedAiModels = [];
        $this->connectionTestResult = null;
        $this->aiModelSearchOpen = false;
        $this->aiModelSearchQuery = '';
        $this->aiAdvancedOpen = false;
        $this->resetValidation();
    }

    public function loadAiConnectionForEdit(int $connectionId): void
    {
        $connection = AiConnection::query()->findOrFail($connectionId);

        $this->editingAiConnectionId = $connection->id;
        $this->aiConnectionForm = self::aiConnectionFormFromModel($connection);
        $this->fetchedAiModels = $connection->available_models ?? [];
        $this->connectionTestResult = null;
        $this->aiModelSearchOpen = false;
        $this->aiModelSearchQuery = '';
        $this->aiAdvancedOpen = false;
        $this->resetValidation();
    }

    public function updatedAiConnectionFormProvider(?string $value): void
    {
        $provider = AiProvider::tryFrom((string) $value);

        if (! $provider) {
            return;
        }

        $this->aiConnectionForm['base_url'] = $provider->defaultBaseUrl();
        $this->fetchedAiModels = [];
        $this->aiConnectionForm['model'] = '';
        $this->aiModelSearchOpen = false;
        $this->aiModelSearchQuery = '';
    }

    public function toggleAiModelSearch(): void
    {
        if ($this->fetchedAiModels === []) {
            return;
        }

        $this->aiModelSearchOpen = ! $this->aiModelSearchOpen;

        if (! $this->aiModelSearchOpen) {
            $this->aiModelSearchQuery = '';
        }
    }

    public function closeAiModelSearch(): void
    {
        $this->aiModelSearchOpen = false;
        $this->aiModelSearchQuery = '';
    }

    public function selectAiModel(string $modelId): void
    {
        $this->selectFetchedAiModel($modelId);
        $this->closeAiModelSearch();
    }

    public function clearAiModelSelection(): void
    {
        $this->aiConnectionForm['model'] = '';
        $this->closeAiModelSearch();
    }

    public function toggleAiAdvancedSettings(): void
    {
        $this->aiAdvancedOpen = ! $this->aiAdvancedOpen;
    }

    public function selectedAiModelLabel(): string
    {
        $modelId = (string) ($this->aiConnectionForm['model'] ?? '');

        if ($modelId === '') {
            return $this->fetchedAiModels === []
                ? 'Fetch models first'
                : 'Search and select a model';
        }

        foreach ($this->fetchedAiModels as $model) {
            if (($model['id'] ?? '') === $modelId) {
                return (string) ($model['name'] ?? $model['id']);
            }
        }

        return $modelId;
    }

    /**
     * @return list<array{id: string, name: string, context_window: int|null, max_output_tokens: int|null}>
     */
    public function filteredFetchedAiModels(): array
    {
        $query = strtolower(trim($this->aiModelSearchQuery));

        if ($query === '') {
            return array_slice($this->fetchedAiModels, 0, (int) config('ai.models.picker_preview_limit', 80));
        }

        return array_values(array_filter(
            $this->fetchedAiModels,
            function (array $model) use ($query): bool {
                $id = strtolower((string) ($model['id'] ?? ''));
                $name = strtolower((string) ($model['name'] ?? ''));

                return str_contains($id, $query) || str_contains($name, $query);
            },
        ));
    }

    public function hasFetchedAiModels(): bool
    {
        return $this->fetchedAiModels !== [];
    }

    public function fetchAiModels(): void
    {
        try {
            $config = $this->resolveAiConnectionConfigForFetch();
            $models = app(AiService::class)->fetchModelsFromConfig($config);

            $this->fetchedAiModels = array_map(
                fn ($model) => $model->toArray(),
                $models,
            );

            $this->aiModelSearchOpen = true;
            $this->aiModelSearchQuery = '';

            if ($this->editingAiConnectionId) {
                $connection = AiConnection::query()->find($this->editingAiConnectionId);

                if ($connection) {
                    AiConnectionManager::storeAvailableModels($connection, $models);
                }
            }

            Notification::make()
                ->title('Models fetched successfully')
                ->body(count($this->fetchedAiModels).' models loaded. Select one and save.')
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Unable to fetch models')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updatedAiConnectionFormModel(?string $value): void
    {
        if (filled($value)) {
            $this->applyModelDefaultsFromFetch($value);
        }
    }

    public function saveAiConnection(bool $testAfterSave = false): void
    {
        $this->authorizePageAction(
            $this->editingAiConnectionId ? PermissionAction::Update : PermissionAction::Create,
        );

        $payload = $this->validateAiConnectionForm();

        if ($this->editingAiConnectionId) {
            $connection = AiConnection::query()->findOrFail($this->editingAiConnectionId);
            $connection = AiConnectionManager::update($connection, $payload);
            $message = 'AI connection updated successfully.';
        } else {
            $connection = AiConnectionManager::create($payload);
            $this->editingAiConnectionId = $connection->id;
            $message = 'AI connection created successfully.';
        }

        unset($this->filteredAiConnections);

        if ($testAfterSave) {
            $result = app(AiService::class)->testConnection($connection->refresh());
            $this->connectionTestResult = self::testResultToArray($result);
            $this->fetchedAiModels = $connection->available_models ?? $this->fetchedAiModels;
            $this->aiConnectionForm = self::aiConnectionFormFromModel($connection);

            Notification::make()->title($message)->success()->send();
            $this->notifyTestResult($result);
            $this->halt();

            return;
        }

        $this->completeModalSave(
            addAnother: false,
            title: $message,
        );
    }

    public function testAiConnection(int $connectionId): void
    {
        $this->authorizePageAction(PermissionAction::Update);

        $connection = AiConnection::query()->findOrFail($connectionId);
        $result = app(AiService::class)->testConnection($connection);

        unset($this->filteredAiConnections);

        $this->notifyTestResult($result);
    }

    public function deleteAiConnection(int $connectionId): void
    {
        $this->authorizePageAction(PermissionAction::Delete);

        $connection = AiConnection::query()->findOrFail($connectionId);
        AiConnectionManager::delete($connection);

        if ($this->editingAiConnectionId === $connectionId) {
            $this->resetAiConnectionForm();
        }

        unset($this->filteredAiConnections);

        Notification::make()
            ->title('AI connection deleted')
            ->success()
            ->send();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function aiConnectionProviderOptions(): array
    {
        $options = AiProvider::settingsOptions();

        if ($this->editingAiConnectionId) {
            $connection = AiConnection::query()->find($this->editingAiConnectionId);

            if ($connection && ! $connection->provider->isImplemented()) {
                array_unshift($options, [
                    'value' => $connection->provider->value,
                    'label' => $connection->provider->label().' (not implemented)',
                ]);
            }
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAiConnectionForm(): array
    {
        $this->aiConnectionForm = array_merge(
            self::aiConnectionTechnicalDefaults(),
            $this->aiConnectionForm,
        );

        $implementedProviders = array_column(AiProvider::settingsOptions(), 'value');

        if ($this->editingAiConnectionId) {
            $existingProvider = AiConnection::query()->find($this->editingAiConnectionId)?->provider;

            if ($existingProvider && ! in_array($existingProvider->value, $implementedProviders, true)) {
                $implementedProviders[] = $existingProvider->value;
            }
        }

        $rules = [
            'aiConnectionForm.provider' => ['required', Rule::in($implementedProviders)],
            'aiConnectionForm.base_url' => ['required', 'url', 'max:500'],
            'aiConnectionForm.model' => ['required', 'string', 'max:255'],
            'aiConnectionForm.context_window' => ['required', 'integer', 'min:1024', 'max:2000000'],
            'aiConnectionForm.max_output_tokens' => ['required', 'integer', 'min:256', 'max:200000'],
            'aiConnectionForm.temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'aiConnectionForm.timeout' => ['required', 'integer', 'min:5', 'max:300'],
            'aiConnectionForm.retry_count' => ['required', 'integer', 'min:0', 'max:5'],
            'aiConnectionForm.is_active' => ['required', 'in:0,1'],
            'aiConnectionForm.is_default' => ['required', 'in:0,1'],
        ];

        $selectedProvider = AiProvider::tryFrom((string) ($this->aiConnectionForm['provider'] ?? ''));

        if ($selectedProvider !== AiProvider::Ollama) {
            $rules['aiConnectionForm.base_url'][] = new PublicAiEndpointUrl;
        }

        if ($selectedProvider === AiProvider::AzureOpenAi) {
            $rules['aiConnectionForm.base_url'][] = 'min:10';
        }

        if ($this->editingAiConnectionId) {
            $rules['aiConnectionForm.api_key'] = ['nullable', 'string', 'max:5000'];
        } elseif (AiProvider::tryFrom((string) ($this->aiConnectionForm['provider'] ?? '')) === AiProvider::Ollama) {
            $rules['aiConnectionForm.api_key'] = ['nullable', 'string', 'max:5000'];
        } else {
            $rules['aiConnectionForm.api_key'] = ['required', 'string', 'max:5000'];
        }

        $validated = $this->validate($rules)['aiConnectionForm'];

        $provider = AiProvider::from($validated['provider']);

        $payload = [
            'provider' => $provider->value,
            'base_url' => rtrim($validated['base_url'], '/'),
            'model' => $validated['model'],
            'context_window' => (int) $validated['context_window'],
            'max_output_tokens' => (int) $validated['max_output_tokens'],
            'temperature' => (float) $validated['temperature'],
            'timeout' => (int) $validated['timeout'],
            'retry_count' => (int) $validated['retry_count'],
            'is_active' => $validated['is_active'] === '1',
            'is_default' => $validated['is_default'] === '1',
        ];

        if ($this->editingAiConnectionId) {
            if (filled($validated['api_key'] ?? null)) {
                $payload['api_key'] = $validated['api_key'];
            }
        } else {
            $payload['api_key'] = $validated['api_key'] ?? '';
        }

        if ($this->editingAiConnectionId && ! isset($payload['api_key'])) {
            $existing = AiConnection::query()->findOrFail($this->editingAiConnectionId);
            $payload['api_key'] = $existing->api_key;
        }

        if ($this->fetchedAiModels !== []) {
            $payload['available_models'] = $this->fetchedAiModels;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveAiConnectionConfigForFetch(): array
    {
        return $this->resolveAiConnectionConfig(requireApiKey: true, requireModel: false);
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveAiConnectionConfig(bool $requireApiKey = false, bool $requireModel = false): array
    {
        $provider = AiProvider::tryFrom((string) ($this->aiConnectionForm['provider'] ?? ''));

        if (! $provider) {
            throw ValidationException::withMessages([
                'aiConnectionForm.provider' => 'Select a provider first.',
            ]);
        }

        $apiKey = (string) ($this->aiConnectionForm['api_key'] ?? '');

        if ($apiKey === '' && $this->editingAiConnectionId) {
            $apiKey = (string) AiConnection::query()->findOrFail($this->editingAiConnectionId)->api_key;
        }

        $apiKeyRequired = $requireApiKey && $provider !== AiProvider::Ollama;

        if ($apiKeyRequired && $apiKey === '') {
            throw ValidationException::withMessages([
                'aiConnectionForm.api_key' => 'Enter an API key before fetching models or testing.',
            ]);
        }

        $model = (string) ($this->aiConnectionForm['model'] ?? '');

        if ($model === '' && $requireModel) {
            throw ValidationException::withMessages([
                'aiConnectionForm.model' => 'Fetch models first, then select a model before saving or testing.',
            ]);
        }

        if ($model === '') {
            $model = 'openrouter/auto';
        }

        $defaults = AiConfig::connectionDefaults();

        return [
            'provider' => $provider->value,
            'api_key' => $apiKey,
            'base_url' => rtrim((string) ($this->aiConnectionForm['base_url'] ?? $provider->defaultBaseUrl()), '/'),
            'model' => $model,
            'context_window' => (int) ($this->aiConnectionForm['context_window'] ?? $defaults['context_window']),
            'max_output_tokens' => (int) ($this->aiConnectionForm['max_output_tokens'] ?? $defaults['max_output_tokens']),
            'temperature' => (float) ($this->aiConnectionForm['temperature'] ?? $defaults['temperature']),
            'timeout' => (int) ($this->aiConnectionForm['timeout'] ?? $defaults['timeout']),
            'retry_count' => (int) ($this->aiConnectionForm['retry_count'] ?? $defaults['retry_count']),
        ];
    }

    protected function selectFetchedAiModel(string $modelId): void
    {
        $this->aiConnectionForm['model'] = $modelId;
        $this->applyModelDefaultsFromFetch($modelId);
    }

    protected function applyModelDefaultsFromFetch(string $modelId): void
    {
        foreach ($this->fetchedAiModels as $model) {
            if (($model['id'] ?? '') !== $modelId) {
                continue;
            }

            if (filled($model['context_window'] ?? null)) {
                $this->aiConnectionForm['context_window'] = (int) $model['context_window'];
            }

            if (filled($model['max_output_tokens'] ?? null)) {
                $this->aiConnectionForm['max_output_tokens'] = (int) $model['max_output_tokens'];
            }

            break;
        }
    }

    protected function notifyTestResult(AiTestResult $result): void
    {
        $details = collect($result->details())
            ->filter(fn (?string $value, string $key) => $key !== 'Error' || filled($value))
            ->map(fn (?string $value, string $key) => "{$key}: {$value}")
            ->implode("\n");

        Notification::make()
            ->title($result->headline())
            ->body($details)
            ->{$result->success ? 'success' : 'danger'}()
            ->send();
    }

    /**
     * @return array<string, int|float>
     */
    protected static function aiConnectionTechnicalDefaults(): array
    {
        return AiConfig::connectionDefaults();
    }

    protected static function emptyAiConnectionForm(): array
    {
        $defaultProvider = AiProvider::OpenRouter;

        return [
            'provider' => $defaultProvider->value,
            'api_key' => '',
            'base_url' => $defaultProvider->defaultBaseUrl(),
            'model' => '',
            ...self::aiConnectionTechnicalDefaults(),
            'is_active' => '1',
            'is_default' => '0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function aiConnectionFormFromModel(AiConnection $connection): array
    {
        return [
            'provider' => $connection->provider->value,
            'api_key' => '',
            'base_url' => $connection->base_url,
            'model' => $connection->model,
            'context_window' => $connection->context_window,
            'max_output_tokens' => $connection->max_output_tokens,
            'temperature' => $connection->temperature,
            'timeout' => $connection->timeout,
            'retry_count' => $connection->retry_count,
            'is_active' => $connection->is_active ? '1' : '0',
            'is_default' => $connection->is_default ? '1' : '0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function testResultToArray(AiTestResult $result): array
    {
        return [
            'headline' => $result->headline(),
            'success' => $result->success,
            'details' => $result->details(),
        ];
    }
}
