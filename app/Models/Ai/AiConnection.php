<?php

namespace App\Models\Ai;

use App\Core\Ai\Enums\AiConnectionTestStatus;
use App\Core\Ai\Enums\AiProvider;
use Illuminate\Database\Eloquent\Model;

class AiConnection extends Model
{
    protected $table = 'ai_connections';

    /** @var list<string> */
    protected $hidden = [
        'api_key',
    ];

    protected $fillable = [
        'provider',
        'api_key',
        'base_url',
        'model',
        'context_window',
        'max_output_tokens',
        'temperature',
        'timeout',
        'retry_count',
        'is_active',
        'is_default',
        'last_tested_at',
        'last_test_status',
        'last_test_response_time_ms',
        'last_test_rate_limit_remaining',
        'last_test_error',
        'available_models',
    ];

    protected function casts(): array
    {
        return [
            'provider' => AiProvider::class,
            'api_key' => 'encrypted',
            'context_window' => 'integer',
            'max_output_tokens' => 'integer',
            'temperature' => 'float',
            'timeout' => 'integer',
            'retry_count' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_tested_at' => 'datetime',
            'last_test_status' => AiConnectionTestStatus::class,
            'last_test_response_time_ms' => 'integer',
            'available_models' => 'array',
        ];
    }

    public function statusLabel(): string
    {
        if (! $this->last_test_status instanceof AiConnectionTestStatus) {
            return 'Disconnected';
        }

        return $this->last_test_status->label();
    }

    public function statusBadgeClass(): string
    {
        if (! $this->last_test_status instanceof AiConnectionTestStatus) {
            return 'agricart-users-list__badge--inactive';
        }

        return $this->last_test_status->listBadgeClass();
    }

    public function lastTestedLabel(): string
    {
        return $this->last_tested_at?->format('Y-m-d H:i') ?? '—';
    }
}
