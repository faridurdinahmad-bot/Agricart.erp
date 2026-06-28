<?php

namespace App\Models\Ai;

use App\Core\Ai\Enums\AiPromptOutputFormat;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use Illuminate\Database\Eloquent\Model;

class AiPromptTemplate extends Model
{
    protected $table = 'ai_prompt_templates';

    protected $fillable = [
        'name',
        'target_module',
        'task_type',
        'system_prompt',
        'user_prompt_template',
        'output_format',
        'temperature',
        'max_output_tokens',
        'is_active',
        'available_variables',
    ];

    protected function casts(): array
    {
        return [
            'target_module' => AiTargetModule::class,
            'task_type' => AiTaskType::class,
            'output_format' => AiPromptOutputFormat::class,
            'temperature' => 'float',
            'max_output_tokens' => 'integer',
            'is_active' => 'boolean',
            'available_variables' => 'array',
        ];
    }

    public function statusLabel(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function statusBadgeClass(): string
    {
        return $this->is_active
            ? 'agricart-users-list__badge--active'
            : 'agricart-users-list__badge--inactive';
    }

    public function updatedLabel(): string
    {
        return $this->updated_at?->format('Y-m-d H:i') ?? '—';
    }

    /**
     * @return list<string>
     */
    public function variablePreviewList(): array
    {
        return $this->available_variables ?? [];
    }
}
