<?php

namespace App\Modules\Settings\Pages;

use App\Core\Ai\Enums\AiProvider;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Ai\AiJobHistory;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class AiLogsPage extends BaseModulePage
{
    use WithPagination;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Logs';

    protected static ?string $title = 'AI Logs';

    protected static ?string $slug = 'ai-logs';

    protected static ?int $navigationSort = SettingsNavigation::AI_LOGS;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public string $search = '';

    public string $providerFilter = '';

    public string $taskTypeFilter = '';

    public string $targetModuleFilter = '';

    public string $statusFilter = '';

    public int $perPage = 25;

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['agricart-ai-logs-page'];
    }

    #[Computed]
    public function aiJobHistories()
    {
        $query = AiJobHistory::query()
            ->with(['user', 'connection'])
            ->orderByDesc('created_at');

        if ($this->providerFilter !== '') {
            $query->where('provider', $this->providerFilter);
        }

        if ($this->taskTypeFilter !== '') {
            $query->where('task_type', $this->taskTypeFilter);
        }

        if ($this->targetModuleFilter !== '') {
            $query->where('target_module', $this->targetModuleFilter);
        }

        if ($this->statusFilter === 'success') {
            $query->where('success', true);
        } elseif ($this->statusFilter === 'failed') {
            $query->where('success', false);
        }

        if (filled($this->search)) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->whereRaw('LOWER(model) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(error_message, \'\')) LIKE ?', [$term])
                    ->orWhereHas('user', function ($userQuery) use ($term): void {
                        $userQuery
                            ->whereRaw('LOWER(name) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                    });
            });
        }

        return $query->paginate($this->perPage);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed]
    public function providerFilterOptions(): array
    {
        return AiProvider::options();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed]
    public function taskTypeFilterOptions(): array
    {
        return AiTaskType::options();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    #[Computed]
    public function targetModuleFilterOptions(): array
    {
        return AiTargetModule::options();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->aiJobHistories);
    }

    public function updatedProviderFilter(): void
    {
        $this->resetPage();
        unset($this->aiJobHistories);
    }

    public function updatedTaskTypeFilter(): void
    {
        $this->resetPage();
        unset($this->aiJobHistories);
    }

    public function updatedTargetModuleFilter(): void
    {
        $this->resetPage();
        unset($this->aiJobHistories);
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        unset($this->aiJobHistories);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.settings.ai-logs-page'),
            ]);
    }
}
