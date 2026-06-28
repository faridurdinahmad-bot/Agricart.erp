<?php

namespace App\Core\Ai\Concerns;

use App\Core\Ai\Enums\AiContentStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasAiContentStatus
{
    public function initializeHasAiContentStatus(): void
    {
        $this->mergeCasts([
            'ai_content_status' => AiContentStatus::class,
        ]);
    }

    public function markAiPending(): void
    {
        $this->update(['ai_content_status' => AiContentStatus::AiPending]);
    }

    public function markAiComplete(): void
    {
        $this->update(['ai_content_status' => AiContentStatus::Complete]);
    }

    public function markAiFailed(): void
    {
        $this->update(['ai_content_status' => AiContentStatus::AiFailed]);
    }

    public function markNeedsReview(): void
    {
        $this->update(['ai_content_status' => AiContentStatus::NeedsReview]);
    }

    public function needsAiAttention(): bool
    {
        $status = $this->ai_content_status;

        return $status instanceof AiContentStatus
            ? $status->needsAiAttention()
            : true;
    }

    public function aiStatusLabel(): string
    {
        $status = $this->ai_content_status;

        return $status instanceof AiContentStatus
            ? $status->label()
            : AiContentStatus::AiPending->label();
    }

    public function aiStatusBadgeClass(): string
    {
        $status = $this->ai_content_status;

        return $status instanceof AiContentStatus
            ? $status->listBadgeClass()
            : AiContentStatus::AiPending->listBadgeClass();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeNeedsAiAttention(Builder $query): Builder
    {
        return $query->whereIn('ai_content_status', [
            AiContentStatus::AiPending->value,
            AiContentStatus::AiFailed->value,
            AiContentStatus::NeedsReview->value,
        ]);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeAiComplete(Builder $query): Builder
    {
        return $query->where('ai_content_status', AiContentStatus::Complete->value);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeAiPending(Builder $query): Builder
    {
        return $query->where('ai_content_status', AiContentStatus::AiPending->value);
    }
}
