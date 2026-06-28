<?php

namespace App\Modules\Settings\Pages;

use App\Core\Ai\Enums\AiConnectionTestStatus;
use App\Core\Ai\Enums\AiProvider;
use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Ai\AiConnection;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Concerns\InteractsWithAiConnectionForm;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Actions\Action;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;

class AiSettingsPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal, InteractsWithAiConnectionForm;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'AI';

    protected static ?string $title = 'AI Settings';

    protected static ?string $slug = 'ai-settings';

    protected static ?int $navigationSort = SettingsNavigation::AI_SETTINGS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    public string $search = '';

    public string $providerFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->resetAiConnectionForm();
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-ai-settings-page'];
    }

    #[Computed]
    public function filteredAiConnections()
    {
        $query = AiConnection::query()->orderByDesc('is_default')->orderBy('provider');

        if ($this->providerFilter !== '') {
            $query->where('provider', $this->providerFilter);
        }

        if ($this->statusFilter === 'connected') {
            $query->where('last_test_status', AiConnectionTestStatus::Connected->value);
        } elseif ($this->statusFilter === 'disconnected') {
            $query->where(function ($builder): void {
                $builder
                    ->whereNull('last_test_status')
                    ->orWhere('last_test_status', '!=', AiConnectionTestStatus::Connected->value);
            });
        }

        if (filled($this->search)) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->whereRaw('LOWER(provider) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(model) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(base_url) LIKE ?', [$term]);
            });
        }

        return $query->get();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed]
    public function providerFilterOptions(): array
    {
        return AiProvider::options();
    }

    public function updatedSearch(): void
    {
        unset($this->filteredAiConnections);
    }

    public function updatedProviderFilter(): void
    {
        unset($this->filteredAiConnections);
    }

    public function updatedStatusFilter(): void
    {
        unset($this->filteredAiConnections);
    }

    public function addAiConnectionAction(): Action
    {
        return $this->configureAiConnectionFormAction(
            Action::make('addAiConnection')
                ->label('Add AI Connection')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->before(function (): void {
                    $this->resetAiConnectionForm();
                }),
        );
    }

    public function aiConnectionFormAction(): Action
    {
        return $this->configureAiConnectionFormAction(Action::make('aiConnectionForm'));
    }

    public function openEditAiConnection(int $connectionId): void
    {
        $this->authorizePageAction(PermissionAction::Update);
        $this->loadAiConnectionForEdit($connectionId);
        $this->mountAction('aiConnectionForm');
    }

    protected function configureAiConnectionFormAction(Action $action): Action
    {
        return $action
            ->modalHeading(fn (): string => $this->editingAiConnectionId ? 'Edit AI Connection' : 'Add AI Connection')
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.settings.ai-connection-form', [
                'live' => true,
                'editingAiConnectionId' => $this->editingAiConnectionId,
                'aiConnectionForm' => $this->aiConnectionForm,
                'fetchedAiModels' => $this->fetchedAiModels,
                'connectionTestResult' => $this->connectionTestResult,
                'providerOptions' => $this->aiConnectionProviderOptions(),
                'aiModelSearchOpen' => $this->aiModelSearchOpen,
                'aiModelSearchQuery' => $this->aiModelSearchQuery,
                'aiAdvancedOpen' => $this->aiAdvancedOpen,
            ]))
            ->stickyModalFooter()
            ->modalFooterActions([
                Action::make('cancelAiConnectionForm')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                Action::make('saveAndTestAiConnection')
                    ->label('Save & Test Connection')
                    ->outlined()
                    ->action(function (): void {
                        $this->saveAiConnection(testAfterSave: true);
                    }),
                Action::make('submitAiConnectionForm')
                    ->label('Save')
                    ->color('primary')
                    ->action(function (): void {
                        $this->saveAiConnection();
                    }),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.settings.ai-connections-page'),
            ]);
    }
}
