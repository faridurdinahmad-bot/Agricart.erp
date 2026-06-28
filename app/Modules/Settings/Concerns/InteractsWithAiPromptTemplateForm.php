<?php

namespace App\Modules\Settings\Concerns;

use App\Core\Ai\Enums\AiPromptOutputFormat;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Services\AiPromptTemplateManager;
use App\Core\Ai\Support\AiConfig;
use App\Core\Ai\Support\AiPromptVariableRegistry;
use App\Core\Authorization\Enums\PermissionAction;
use App\Models\Ai\AiPromptTemplate;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait InteractsWithAiPromptTemplateForm
{
    public ?int $editingAiPromptTemplateId = null;

    /** @var array<string, mixed> */
    public array $aiPromptTemplateForm = [];

    public function resetAiPromptTemplateForm(): void
    {
        $this->editingAiPromptTemplateId = null;
        $this->aiPromptTemplateForm = self::emptyAiPromptTemplateForm();
        $this->resetValidation();
    }

    public function loadAiPromptTemplateForEdit(int $templateId): void
    {
        $template = AiPromptTemplate::query()->findOrFail($templateId);

        $this->editingAiPromptTemplateId = $template->id;
        $this->aiPromptTemplateForm = self::aiPromptTemplateFormFromModel($template);
        $this->resetValidation();
    }

    public function updatedAiPromptTemplateFormTaskType(?string $value): void
    {
        $taskType = AiTaskType::tryFrom((string) $value);

        if (! $taskType) {
            return;
        }

        $this->aiPromptTemplateForm['available_variables'] = AiPromptVariableRegistry::forTaskType($taskType);

        if ($taskType->requiresJsonResponse()) {
            $this->aiPromptTemplateForm['output_format'] = AiPromptOutputFormat::Json->value;
        }
    }

    /**
     * @return list<string>
     */
    public function promptTemplateVariablePreview(): array
    {
        $taskType = AiTaskType::tryFrom((string) ($this->aiPromptTemplateForm['task_type'] ?? ''));

        if (! $taskType) {
            return AiPromptVariableRegistry::commonVariables();
        }

        return $this->aiPromptTemplateForm['available_variables']
            ?? AiPromptVariableRegistry::forTaskType($taskType);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function aiPromptTemplateTaskTypeOptions(): array
    {
        return array_values(array_filter(
            AiTaskType::moduleTaskOptions(),
            fn (array $option): bool => $option['value'] !== AiTaskType::ConnectionTest->value,
        ));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function aiPromptTemplateModuleOptions(): array
    {
        return AiTargetModule::options();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function aiPromptTemplateOutputFormatOptions(): array
    {
        return AiPromptOutputFormat::options();
    }

    public function saveAiPromptTemplate(bool $addAnother = false): void
    {
        $this->authorizePageAction(
            $this->editingAiPromptTemplateId
                ? PermissionAction::Update
                : PermissionAction::Create,
        );

        try {
            $payload = $this->validateAiPromptTemplateForm();

            if ($this->editingAiPromptTemplateId) {
                $template = AiPromptTemplate::query()->findOrFail($this->editingAiPromptTemplateId);
                AiPromptTemplateManager::update($template, $payload);
                $message = 'Prompt template updated successfully.';
            } else {
                AiPromptTemplateManager::create($payload);
                $message = 'Prompt template created successfully.';
            }

            unset($this->filteredAiPromptTemplates);

            $this->resetAiPromptTemplateForm();

            if ($addAnother) {
                Notification::make()->title($message)->success()->send();
                $this->replaceMountedAction('addAiPromptTemplate');

                return;
            }

            $this->completeModalSave(
                addAnother: false,
                title: $message,
                refreshNavigation: true,
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAiPromptTemplateForm(): array
    {
        $validated = $this->validate([
            'aiPromptTemplateForm.name' => ['required', 'string', 'max:255'],
            'aiPromptTemplateForm.target_module' => ['required', Rule::enum(AiTargetModule::class)],
            'aiPromptTemplateForm.task_type' => ['required', Rule::enum(AiTaskType::class)],
            'aiPromptTemplateForm.system_prompt' => ['required', 'string', 'max:'.AiConfig::promptSystemMaxLength()],
            'aiPromptTemplateForm.user_prompt_template' => ['required', 'string', 'max:'.AiConfig::promptUserMaxLength()],
            'aiPromptTemplateForm.output_format' => ['required', Rule::enum(AiPromptOutputFormat::class)],
            'aiPromptTemplateForm.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'aiPromptTemplateForm.max_output_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'aiPromptTemplateForm.is_active' => ['required', 'in:0,1'],
        ])['aiPromptTemplateForm'];

        $taskType = AiTaskType::from((string) $validated['task_type']);

        if ($taskType === AiTaskType::ConnectionTest) {
            throw ValidationException::withMessages([
                'aiPromptTemplateForm.task_type' => 'Connection test prompts are managed internally and cannot be edited here.',
            ]);
        }

        return [
            'name' => trim((string) $validated['name']),
            'target_module' => AiTargetModule::from((string) $validated['target_module']),
            'task_type' => $taskType,
            'system_prompt' => trim((string) $validated['system_prompt']),
            'user_prompt_template' => trim((string) $validated['user_prompt_template']),
            'output_format' => AiPromptOutputFormat::from((string) $validated['output_format']),
            'temperature' => filled($validated['temperature'] ?? null)
                ? (float) $validated['temperature']
                : null,
            'max_output_tokens' => filled($validated['max_output_tokens'] ?? null)
                ? (int) $validated['max_output_tokens']
                : null,
            'is_active' => ($validated['is_active'] ?? '1') === '1' || $validated['is_active'] === true,
            'available_variables' => AiPromptVariableRegistry::forTaskType($taskType),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function emptyAiPromptTemplateForm(): array
    {
        return [
            'name' => '',
            'target_module' => AiTargetModule::Catalog->value,
            'task_type' => AiTaskType::CategoryContent->value,
            'system_prompt' => '',
            'user_prompt_template' => '',
            'output_format' => AiPromptOutputFormat::Json->value,
            'temperature' => '',
            'max_output_tokens' => '',
            'is_active' => true,
            'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::CategoryContent),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function aiPromptTemplateFormFromModel(AiPromptTemplate $template): array
    {
        return [
            'name' => $template->name,
            'target_module' => $template->target_module->value,
            'task_type' => $template->task_type->value,
            'system_prompt' => $template->system_prompt,
            'user_prompt_template' => $template->user_prompt_template,
            'output_format' => $template->output_format->value,
            'temperature' => $template->temperature ?? '',
            'max_output_tokens' => $template->max_output_tokens ?? '',
            'is_active' => $template->is_active,
            'available_variables' => $template->variablePreviewList(),
        ];
    }
}
