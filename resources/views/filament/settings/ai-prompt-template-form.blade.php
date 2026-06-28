<div class="agricart-ai-prompt-template-form">
    <div class="agricart-ai-prompt-template-form__grid agricart-ai-prompt-template-form__grid--2">
        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label agricart-ai-prompt-template-form__label--required" for="prompt_template_name">Prompt Name</label>
            <input
                id="prompt_template_name"
                type="text"
                class="agricart-ai-prompt-template-form__control"
                placeholder="e.g. Category Content"
                wire:model="aiPromptTemplateForm.name"
            >
            @error('aiPromptTemplateForm.name') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label agricart-ai-prompt-template-form__label--required" for="prompt_template_module">Module</label>
            <select id="prompt_template_module" class="agricart-ai-prompt-template-form__control" wire:model="aiPromptTemplateForm.target_module">
                @foreach ($this->aiPromptTemplateModuleOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @error('aiPromptTemplateForm.target_module') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="agricart-ai-prompt-template-form__grid agricart-ai-prompt-template-form__grid--3">
        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label agricart-ai-prompt-template-form__label--required" for="prompt_template_task_type">Task Type</label>
            <select id="prompt_template_task_type" class="agricart-ai-prompt-template-form__control" wire:model.live="aiPromptTemplateForm.task_type">
                @foreach ($this->aiPromptTemplateTaskTypeOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @error('aiPromptTemplateForm.task_type') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label agricart-ai-prompt-template-form__label--required" for="prompt_template_output_format">Output Format</label>
            <select id="prompt_template_output_format" class="agricart-ai-prompt-template-form__control" wire:model="aiPromptTemplateForm.output_format">
                @foreach ($this->aiPromptTemplateOutputFormatOptions() as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @error('aiPromptTemplateForm.output_format') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
        </div>

        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label" for="prompt_template_is_active">Active</label>
            <select id="prompt_template_is_active" class="agricart-ai-prompt-template-form__control" wire:model="aiPromptTemplateForm.is_active">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            @error('aiPromptTemplateForm.is_active') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="agricart-ai-prompt-template-form__grid agricart-ai-prompt-template-form__grid--2">
        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label" for="prompt_template_temperature">Temperature</label>
            <input
                id="prompt_template_temperature"
                type="number"
                min="0"
                max="2"
                step="0.01"
                class="agricart-ai-prompt-template-form__control"
                placeholder="Use connection default"
                wire:model="aiPromptTemplateForm.temperature"
            >
            @error('aiPromptTemplateForm.temperature') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
            <p class="agricart-ai-prompt-template-form__hint">Leave blank to use the AI connection default.</p>
        </div>

        <div class="agricart-ai-prompt-template-form__field">
            <label class="agricart-ai-prompt-template-form__label" for="prompt_template_max_output_tokens">Max Output Tokens</label>
            <input
                id="prompt_template_max_output_tokens"
                type="number"
                min="1"
                class="agricart-ai-prompt-template-form__control"
                placeholder="Use connection default"
                wire:model="aiPromptTemplateForm.max_output_tokens"
            >
            @error('aiPromptTemplateForm.max_output_tokens') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
            <p class="agricart-ai-prompt-template-form__hint">Leave blank to use the AI connection default.</p>
        </div>
    </div>

    <div class="agricart-ai-prompt-template-form__field">
        <label class="agricart-ai-prompt-template-form__label agricart-ai-prompt-template-form__label--required" for="prompt_template_system_prompt">System Prompt</label>
        <textarea
            id="prompt_template_system_prompt"
            rows="6"
            class="agricart-ai-prompt-template-form__control agricart-ai-prompt-template-form__control--textarea"
            placeholder="Instructions for the AI model role and behavior..."
            wire:model="aiPromptTemplateForm.system_prompt"
        ></textarea>
        @error('aiPromptTemplateForm.system_prompt') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
    </div>

    <div class="agricart-ai-prompt-template-form__field">
        <label class="agricart-ai-prompt-template-form__label agricart-ai-prompt-template-form__label--required" for="prompt_template_user_prompt">User Prompt Template</label>
        <textarea
            id="prompt_template_user_prompt"
            rows="10"
            class="agricart-ai-prompt-template-form__control agricart-ai-prompt-template-form__control--textarea"
            placeholder="Task instructions with variables such as {{english_name}}..."
            wire:model="aiPromptTemplateForm.user_prompt_template"
        ></textarea>
        @error('aiPromptTemplateForm.user_prompt_template') <span class="agricart-ai-prompt-template-form__error">{{ $message }}</span> @enderror
    </div>

    <section class="agricart-ai-prompt-template-form__variables">
        <h3 class="agricart-ai-prompt-template-form__variables-title">Available Variables</h3>
        <p class="agricart-ai-prompt-template-form__variables-text">
            These placeholders are replaced automatically when AI runs. Use them inside your user prompt template.
        </p>
        <div class="agricart-ai-prompt-template-form__variables-list">
            @foreach ($this->promptTemplateVariablePreview() as $variable)
                <code class="agricart-ai-prompt-template-form__variable-chip">{{ $variable }}</code>
            @endforeach
        </div>
    </section>
</div>
