<?php

namespace App\Models\Ai;

use App\Core\Ai\Enums\AiProvider;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiJobHistory extends Model
{
    public $timestamps = false;

    protected $table = 'ai_job_histories';

    protected $fillable = [
        'user_id',
        'ai_connection_id',
        'provider',
        'model',
        'task_type',
        'target_module',
        'target_type',
        'target_id',
        'success',
        'response_time_ms',
        'tokens_input',
        'tokens_output',
        'tokens_total',
        'estimated_cost',
        'error_message',
        'context_snapshot',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => AiProvider::class,
            'task_type' => AiTaskType::class,
            'target_module' => AiTargetModule::class,
            'success' => 'boolean',
            'response_time_ms' => 'integer',
            'tokens_input' => 'integer',
            'tokens_output' => 'integer',
            'tokens_total' => 'integer',
            'estimated_cost' => 'decimal:6',
            'context_snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<AiConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(AiConnection::class, 'ai_connection_id');
    }

    public function createdAtLabel(): string
    {
        return $this->created_at?->format('Y-m-d H:i:s') ?? '—';
    }

    public function statusLabel(): string
    {
        return $this->success ? 'Success' : 'Failed';
    }

    public function statusBadgeClass(): string
    {
        return $this->success
            ? 'agricart-users-list__badge--active'
            : 'agricart-users-list__badge--inactive';
    }

    public function tokensLabel(): string
    {
        if ($this->tokens_total) {
            return (string) $this->tokens_total;
        }

        if ($this->tokens_input || $this->tokens_output) {
            return trim(($this->tokens_input ?? 0).' / '.($this->tokens_output ?? 0));
        }

        return '—';
    }

    public function estimatedCostLabel(): string
    {
        return $this->estimated_cost !== null
            ? number_format((float) $this->estimated_cost, 6)
            : '—';
    }

    public function targetLabel(): string
    {
        if ($this->target_type && $this->target_id) {
            return $this->target_type.' #'.$this->target_id;
        }

        return '—';
    }
}
