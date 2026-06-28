<?php

namespace App\Core\Ai\Enums;

enum AiContentStatus: string
{
    case Complete = 'complete';
    case AiPending = 'ai_pending';
    case AiFailed = 'ai_failed';
    case NeedsReview = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::Complete => 'Complete',
            self::AiPending => 'AI Pending',
            self::AiFailed => 'AI Failed',
            self::NeedsReview => 'Needs Review',
        };
    }

    public function listBadgeClass(): string
    {
        return match ($this) {
            self::Complete => 'agricart-users-list__badge--active',
            self::AiPending => 'agricart-users-list__badge--pending',
            self::AiFailed => 'agricart-users-list__badge--inactive',
            self::NeedsReview => 'agricart-users-list__badge--warning',
        };
    }

    public function needsAiAttention(): bool
    {
        return in_array($this, [self::AiPending, self::AiFailed, self::NeedsReview], true);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases(),
        );
    }
}
