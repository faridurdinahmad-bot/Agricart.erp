<?php

namespace App\Modules\Settings\Pages;

use App\Core\Ai\Enums\AiTaskType;
use App\Core\Authorization\Concerns\AuthorizesPageActions;
use App\Core\Authorization\Enums\PermissionAction;
use App\Core\Filament\Concerns\HandlesCrudModal;
use App\Core\Filament\Pages\BaseModulePage;
use App\Models\Ai\AiPromptTemplate;
use App\Modules\Settings\Clusters\SettingsCluster;
use App\Modules\Settings\Concerns\InteractsWithAiPromptTemplateForm;
use App\Modules\Settings\Navigation\SettingsNavigation;
use Filament\Actions\Action;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;

class AiPromptTemplatesPage extends BaseModulePage
{
    use AuthorizesPageActions, HandlesCrudModal, InteractsWithAiPromptTemplateForm;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $navigationLabel = 'Prompt Templates';

    protected static ?string $title = 'AI Prompt Templates';

    protected static ?string $slug = 'ai-prompt-templates';

    protected static ?int $navigationSort = SettingsNavigation::AI_PROMPT_TEMPLATES;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public string $search = '';

    public string $moduleFilter = '';

    public string $taskTypeFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->resetAiPromptTemplateForm();
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
        return ['agricart-ai-prompt-templates-page'];
    }

    #[Computed]
    public function filteredAiPromptTemplates()
    {
        $query = AiPromptTemplate::query()
            ->where('task_type', '!=', AiTaskType::ConnectionTest->value)
            ->orderBy('target_module')
            ->orderBy('task_type')
            ->orderBy('name');

        if ($this->moduleFilter !== '') {
            $query->where('target_module', $this->moduleFilter);
        }

        if ($this->taskTypeFilter !== '') {
            $query->where('task_type', $this->taskTypeFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if (filled($this->search)) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(task_type) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(target_module) LIKE ?', [$term]);
            });
        }

        return $query->get();
    }

    public function updatedSearch(): void
    {
        unset($this->filteredAiPromptTemplates);
    }

    public function updatedModuleFilter(): void
    {
        unset($this->filteredAiPromptTemplates);
    }

    public function updatedTaskTypeFilter(): void
    {
        unset($this->filteredAiPromptTemplates);
    }

    public function updatedStatusFilter(): void
    {
        unset($this->filteredAiPromptTemplates);
    }

    public function addAiPromptTemplateAction(): Action
    {
        return $this->configureAiPromptTemplateFormAction(
            Action::make('addAiPromptTemplate')
                ->label('Add Prompt')
                ->icon(Heroicon::OutlinedPlus)
                ->color('primary')
                ->before(function (): void {
                    $this->resetAiPromptTemplateForm();
                }),
        );
    }

    public function aiPromptTemplateFormAction(): Action
    {
        return $this->configureAiPromptTemplateFormAction(Action::make('aiPromptTemplateForm'));
    }

    public function openEditAiPromptTemplate(int $templateId): void
    {
        $this->authorizePageAction(PermissionAction::Update);
        $this->loadAiPromptTemplateForEdit($templateId);
        $this->mountAction('aiPromptTemplateForm');
    }

    protected function configureAiPromptTemplateFormAction(Action $action): Action
    {
        return $action
            ->modalHeading(fn (): string => $this->editingAiPromptTemplateId ? 'Edit Prompt Template' : 'Add Prompt Template')
            ->modalWidth(Width::FourExtraLarge)
            ->modalContent(fn (): \Illuminate\Contracts\View\View => view('filament.settings.ai-prompt-template-form', [
                'live' => true,
            ]))
            ->stickyModalFooter()
            ->modalFooterActions([
                Action::make('cancelAiPromptTemplateForm')
                    ->label('Cancel')
                    ->color('gray')
                    ->close(),
                Action::make('saveAndAddNextAiPromptTemplate')
                    ->label('Save & Add Next')
                    ->outlined()
                    ->visible(fn (): bool => ! $this->editingAiPromptTemplateId)
                    ->action(function (): void {
                        $this->saveAiPromptTemplate(addAnother: true);
                    }),
                Action::make('submitAiPromptTemplateForm')
                    ->label('Save & Close')
                    ->color('primary')
                    ->cancelParentActions()
                    ->action(function (): void {
                        $this->saveAiPromptTemplate();
                    }),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.settings.ai-prompt-templates-page'),
            ]);
    }
}
